<?php

namespace App\EventSubscriber;

use App\Entity\MediaItem;
use App\Service\Media\MediaCropService;
use Doctrine\ORM\Event\OnFlushEventArgs;

final class MediaItemFileSyncSubscriber
{
    public function __construct(
        private readonly MediaCropService $mediaCropService,
    ) {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $classMetadata = $entityManager->getClassMetadata(MediaItem::class);

        foreach (array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates()) as $entity) {
            if (!$entity instanceof MediaItem) {
                continue;
            }

            $this->mediaCropService->sync($entity);
            $unitOfWork->recomputeSingleEntityChangeSet($classMetadata, $entity);
        }
    }
}
