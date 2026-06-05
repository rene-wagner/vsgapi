<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

class MediaCopyService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly MediaStorageService $mediaStorageService,
        private readonly string $storageDir,
    ) {
    }

    public function copy(MediaItem $source, ?MediaFolder $targetFolder = null): MediaItem
    {
        $folder = $targetFolder ?? $source->getFolder();
        $newId = Uuid::v4()->toRfc4122();
        $ext = $source->getExtension() ?? '';
        $filename = $this->mediaStorageService->buildMediaFilename($source->getName() ?? 'kopie', $newId, $ext);
        $newRelative = $this->mediaStorageService->buildItemRelativePath($folder, $filename);
        $dstAbsolute = $this->storageDir . '/' . $newRelative;

        $sourcePath = $source->getPath();
        if ($sourcePath === null || $sourcePath === '') {
            throw new BadRequestHttpException('Quelldatei fehlt.');
        }

        $this->mediaStorageService->copyFile($sourcePath, $newRelative);

        $newThumbRelative = null;
        $sourceThumbRelative = $source->getThumbnailPath();
        if ($sourceThumbRelative !== null && $sourceThumbRelative !== '' && is_file($this->storageDir . '/' . $sourceThumbRelative)) {
            $newThumbRelative = $this->mediaStorageService->buildThumbnailRelativePath($folder, basename($newRelative));
            try {
                $this->mediaStorageService->copyFile($sourceThumbRelative, $newThumbRelative);
            } catch (BadRequestHttpException) {
                $this->logger->error('Media copy: thumbnail copy failed.');
                $newThumbRelative = null;
            }
        }

        $copy = new MediaItem();
        $copy->setFolder($folder);
        $copy->setCategory($source->getCategory());
        $copy->setName($source->getName() ?? 'copy');
        $copy->setOriginalFilename($source->getOriginalFilename() ?? '');
        $copy->setMimeType($source->getMimeType() ?? '');
        $copy->setExtension($ext);
        $itemType = $source->getType();
        if ($itemType === null) {
            throw new \LogicException('Source media item has no type.');
        }
        $copy->setType($itemType);
        $copy->setSizeBytes((int) filesize($dstAbsolute));
        $copy->setPath($newRelative);
        $copy->setThumbnailPath($newThumbRelative);
        $copy->setDescription($source->getDescription());
        $copy->setCropX($source->getCropX());
        $copy->setCropY($source->getCropY());
        $copy->setCropWidth($source->getCropWidth());
        $copy->setCropHeight($source->getCropHeight());

        $this->entityManager->persist($copy);
        $this->entityManager->flush();

        return $copy;
    }
}
