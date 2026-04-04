<?php

namespace App\Service\Media;

class MediaUrlService
{
    public function __construct(
        private readonly string $mediaHost,
        private readonly string $publicPathPrefix,
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

    public function buildFileUrl(string $relativePath): string
    {
        return $this->joinPublicUrl($relativePath);
    }

    public function buildThumbnailUrl(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        return $this->joinPublicUrl($relativePath);
    }

    private function joinPublicUrl(string $relativePath): string
    {
        $host = rtrim($this->mediaHost, '/');
        $prefix = '/' . trim($this->publicPathPrefix, '/');
        $path = ltrim(str_replace('\\', '/', $relativePath), '/');

        return $host . $prefix . '/' . $path;
    }
}
