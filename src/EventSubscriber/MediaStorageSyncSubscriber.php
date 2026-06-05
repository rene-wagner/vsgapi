<?php

namespace App\EventSubscriber;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Service\Media\MediaStorageService;
use Doctrine\ORM\Event\OnFlushEventArgs;

class MediaStorageSyncSubscriber
{
    public function __construct(private readonly MediaStorageService $mediaStorageService)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $folderClassMetadata = $entityManager->getClassMetadata(MediaFolder::class);
        $itemClassMetadata = $entityManager->getClassMetadata(MediaItem::class);

        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof MediaFolder) {
                continue;
            }

            $changes = $this->mediaStorageService->syncFolder($entity, null);
            foreach ($changes['folders'] as $folder) {
                $unitOfWork->recomputeSingleEntityChangeSet($folderClassMetadata, $folder);
            }
            foreach ($changes['items'] as $item) {
                $unitOfWork->recomputeSingleEntityChangeSet($itemClassMetadata, $item);
            }
        }

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof MediaFolder) {
                $changeSet = $unitOfWork->getEntityChangeSet($entity);
                if (!isset($changeSet['name']) && !isset($changeSet['parent'])) {
                    continue;
                }

                $oldPath = $changeSet['storagePath'][0] ?? $entity->getStoragePath();
                $changes = $this->mediaStorageService->syncFolder($entity, $oldPath);
                foreach ($changes['folders'] as $folder) {
                    $unitOfWork->recomputeSingleEntityChangeSet($folderClassMetadata, $folder);
                }
                foreach ($changes['items'] as $item) {
                    $unitOfWork->recomputeSingleEntityChangeSet($itemClassMetadata, $item);
                }

                continue;
            }

            if (!$entity instanceof MediaItem) {
                continue;
            }

            $changeSet = $unitOfWork->getEntityChangeSet($entity);
            if (!isset($changeSet['folder'])) {
                continue;
            }

            $oldPath = $changeSet['path'][0] ?? $entity->getPath();
            $oldThumbnailPath = $changeSet['thumbnailPath'][0] ?? $entity->getThumbnailPath();
            $this->mediaStorageService->syncItemLocation($entity, $oldPath, $oldThumbnailPath);
            $unitOfWork->recomputeSingleEntityChangeSet($itemClassMetadata, $entity);
        }
    }
}
