<?php

namespace App\Service\Media;

use App\Entity\MediaItem;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaCropService
{
    public function __construct(
        private readonly string $storageDir,
        private readonly int $thumbnailMaxEdge,
    ) {
    }

    public function getCroppedRelativePath(MediaItem $item): ?string
    {
        if (!$item->isCroppable() || !$item->hasCropData()) {
            return null;
        }

        return $this->buildCroppedRelativePath($item);
    }

    public function getCroppedThumbnailRelativePath(MediaItem $item): ?string
    {
        if (!$item->isCroppable() || !$item->hasCropData()) {
            return null;
        }

        return $this->buildCroppedThumbnailRelativePath($item);
    }

    public function sync(MediaItem $item): void
    {
        $croppedRelativePath = $this->getCroppedRelativePath($item);
        $croppedThumbnailRelativePath = $this->getCroppedThumbnailRelativePath($item);
        if ($croppedRelativePath === null || $croppedThumbnailRelativePath === null) {
            $this->delete($item);

            return;
        }

        $sourcePath = $item->getPath();
        if ($sourcePath === null || $sourcePath === '') {
            $this->delete($item);

            return;
        }

        $sourceAbsolutePath = $this->storageDir . '/' . $sourcePath;
        if (!is_file($sourceAbsolutePath)) {
            $this->delete($item);

            return;
        }

        $this->ensureDirectoryExists(dirname($this->storageDir . '/' . $croppedRelativePath));
        $this->ensureDirectoryExists(dirname($this->storageDir . '/' . $croppedThumbnailRelativePath));

        $manager = new ImageManager(new Driver());

        $croppedImage = $manager->read($sourceAbsolutePath);
        $croppedImage->crop(
            (int) $item->getCropWidth(),
            (int) $item->getCropHeight(),
            (int) $item->getCropX(),
            (int) $item->getCropY(),
        );

        $croppedAbsolutePath = $this->storageDir . '/' . $croppedRelativePath;
        match ($item->getMimeType()) {
            'image/png' => $croppedImage->toPng()->save($croppedAbsolutePath),
            'image/webp' => $croppedImage->toWebp(quality: 82)->save($croppedAbsolutePath),
            default => $croppedImage->toJpeg(quality: 82)->save($croppedAbsolutePath),
        };

        $croppedThumbnailImage = $manager->read($sourceAbsolutePath);
        $croppedThumbnailImage->crop(
            (int) $item->getCropWidth(),
            (int) $item->getCropHeight(),
            (int) $item->getCropX(),
            (int) $item->getCropY(),
        );
        $croppedThumbnailImage->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);
        $croppedThumbnailImage->toJpeg(quality: 82)->save($this->storageDir . '/' . $croppedThumbnailRelativePath);
    }

    public function delete(MediaItem $item): void
    {
        $croppedRelativePath = $this->buildCroppedRelativePath($item);
        if ($croppedRelativePath !== null) {
            $this->removeFileIfExists($this->storageDir . '/' . $croppedRelativePath);
        }

        $croppedThumbnailRelativePath = $this->buildCroppedThumbnailRelativePath($item);
        if ($croppedThumbnailRelativePath !== null) {
            $this->removeFileIfExists($this->storageDir . '/' . $croppedThumbnailRelativePath);
        }
    }

    private function buildCroppedRelativePath(MediaItem $item): ?string
    {
        if ($item->getId() === null || $item->getExtension() === null || $item->getExtension() === '') {
            return null;
        }

        return 'cropped/' . $item->getId() . '.' . $item->getExtension();
    }

    private function buildCroppedThumbnailRelativePath(MediaItem $item): ?string
    {
        if ($item->getId() === null) {
            return null;
        }

        return 'cropped-thumbnails/' . $item->getId() . '.jpg';
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Das Zielverzeichnis fuer zugeschnittene Medien konnte nicht erstellt werden.');
        }
    }

    private function removeFileIfExists(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
