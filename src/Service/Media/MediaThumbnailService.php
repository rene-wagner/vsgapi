<?php

namespace App\Service\Media;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;

class MediaThumbnailService
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $storageDir,
        private readonly int $thumbnailMaxEdge,
    ) {
    }

    public function generate(string $relativePath, string $thumbRelative): bool
    {
        $relativePath = $this->normalizeStorageRelativePath($relativePath);
        $thumbRelative = $this->normalizeStorageRelativePath($thumbRelative);

        $absolutePath = $this->storageDir . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            $this->logger->error('Media source file for thumbnail generation is missing.', ['path' => $relativePath]);

            return false;
        }

        $thumbAbsolute = $this->storageDir . '/' . $thumbRelative;
        $thumbDir = dirname($thumbAbsolute);
        if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
            $this->logger->error('Thumbnail directory could not be created.', ['dir' => $thumbDir]);

            return false;
        }

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($absolutePath);
            $image->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);
            $image->toJpeg(quality: 82)->save($thumbAbsolute);

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('Media thumbnail generation failed.', [
                'exception' => $e,
                'path' => $relativePath,
            ]);

            return false;
        }
    }

    private function normalizeStorageRelativePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return $path;
    }
}
