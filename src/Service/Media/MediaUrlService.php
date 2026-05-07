<?php

namespace App\Service\Media;

use App\Entity\MediaItem;

class MediaUrlService
{
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