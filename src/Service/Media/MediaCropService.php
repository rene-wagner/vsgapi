<?php

namespace App\Service\Media;

use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class MediaCropService
{
    public function __construct(
        private readonly string $storageDir,
        private readonly int $thumbnailMaxEdge,
    ) {
    }

    public function sync(MediaItem $item): void
    {
        if ($item->getType() !== MediaItemType::Image || \in_array($item->getMimeType(), ['image/svg+xml', 'image/gif'], true)) {
            return;
        }

        $sourcePath = $item->getPath();
        if ($sourcePath === null || $sourcePath === '') {
            return;
        }

        $sourcePath = $this->normalizeStorageRelativePath($sourcePath);
        $sourceAbsolutePath = $this->storageDir . '/' . $sourcePath;
        if (!is_file($sourceAbsolutePath)) {
            return;
        }

        $thumbnailRelativePath = $this->buildOriginalThumbnailRelativePath($sourcePath);
        $legacyCroppedThumbnailRelativePath = $this->buildLegacyCroppedThumbnailRelativePath($sourcePath);

        if ($item->isCroppable() && $item->hasCropData()) {
            $this->generateThumbnail($sourceAbsolutePath, $thumbnailRelativePath, $item);
            $this->removeFileIfExists($this->storageDir . '/' . $legacyCroppedThumbnailRelativePath);
            $item->setThumbnailPath($thumbnailRelativePath);

            return;
        }

        $this->generateThumbnail($sourceAbsolutePath, $thumbnailRelativePath);
        $this->removeFileIfExists($this->storageDir . '/' . $legacyCroppedThumbnailRelativePath);
        $item->setThumbnailPath($thumbnailRelativePath);
    }

    public function delete(MediaItem $item): void
    {
        $sourcePath = $item->getPath();
        if ($sourcePath === null || $sourcePath === '') {
            return;
        }

        $sourcePath = $this->normalizeStorageRelativePath($sourcePath);
        $this->removeFileIfExists($this->storageDir . '/' . $this->buildLegacyCroppedThumbnailRelativePath($sourcePath));
    }

    private function generateThumbnail(string $sourceAbsolutePath, string $thumbnailRelativePath, ?MediaItem $item = null): void
    {
        $thumbnailAbsolutePath = $this->storageDir . '/' . $thumbnailRelativePath;
        $this->ensureDirectoryExists(dirname($thumbnailAbsolutePath));

        $manager = new ImageManager(new Driver());
        $image = $manager->read($sourceAbsolutePath);

        if ($item !== null) {
            $image->crop(
                (int) $item->getCropWidth(),
                (int) $item->getCropHeight(),
                (int) $item->getCropX(),
                (int) $item->getCropY(),
            );
        }

        $image->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);
        $image->toJpeg(quality: 82)->save($thumbnailAbsolutePath);
    }

    private function buildOriginalThumbnailRelativePath(string $sourcePath): string
    {
        return 'thumbnails/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '.jpg';
    }

    private function buildLegacyCroppedThumbnailRelativePath(string $sourcePath): string
    {
        return 'thumbnails/' . pathinfo($sourcePath, PATHINFO_FILENAME) . '-cropped.jpg';
    }

    private function normalizeStorageRelativePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return $path;
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('Das Zielverzeichnis fuer Thumbnails konnte nicht erstellt werden.');
        }
    }

    private function removeFileIfExists(string $path): void
    {
        if (is_file($path)) {
            unlink($path);
        }
    }
}
