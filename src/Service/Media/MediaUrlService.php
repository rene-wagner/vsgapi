<?php

namespace App\Service\Media;

use App\Entity\MediaItem;

class MediaUrlService
{
    public function __construct(
        private readonly MediaCropService $mediaCropService,
    ) {
    }

    public function formatSizeHuman(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024.0;
        $i = 0;
        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            ++$i;
        }

        return round($value, 1) . ' ' . $units[$i];
    }

    public function buildOriginalUrl(MediaItem $item): ?string
    {
        if ($item->getId() === null || $item->getPath() === null || $item->getPath() === '') {
            return null;
        }

        return $this->buildUploadUrl($item->getPath(), $item);
    }

    public function buildThumbnailUrl(MediaItem $item): ?string
    {
        if ($item->getId() === null || $item->getThumbnailPath() === null || $item->getThumbnailPath() === '') {
            return null;
        }

        return $this->buildUploadUrl($item->getThumbnailPath(), $item);
    }

    public function buildCroppedUrl(MediaItem $item): ?string
    {
        if ($item->getId() === null || !$item->isCroppable() || !$item->hasCropData()) {
            return null;
        }

        $relativePath = $this->mediaCropService->getCroppedRelativePath($item);
        if ($relativePath === null) {
            return null;
        }

        return $this->buildUploadUrl($relativePath, $item);
    }

    public function buildCroppedThumbnailUrl(MediaItem $item): ?string
    {
        if ($item->getId() === null || !$item->isCroppable() || !$item->hasCropData()) {
            return null;
        }

        $relativePath = $this->mediaCropService->getCroppedThumbnailRelativePath($item);
        if ($relativePath === null) {
            return null;
        }

        return $this->buildUploadUrl($relativePath, $item);
    }

    public function buildDisplayUrl(MediaItem $item): ?string
    {
        $cropped = $this->buildCroppedUrl($item);
        if ($cropped !== null) {
            return $cropped;
        }

        return $this->buildOriginalUrl($item);
    }

    private function buildUploadUrl(string $path, MediaItem $item): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return $this->appendVersion('/uploads/' . $path, $item);
    }

    private function appendVersion(string $url, MediaItem $item): string
    {
        $updatedAt = $item->getUpdatedAt();
        if ($updatedAt !== null) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'v=' . $updatedAt->getTimestamp();
        }

        return $url;
    }
}