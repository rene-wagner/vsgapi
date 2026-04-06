<?php

namespace App\Service\Media;

use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;

class MediaImageCropService
{
    private const NATURAL_TOLERANCE = 2;

    public function __construct(
        private readonly string $storageDir,
        private readonly int $thumbnailMaxEdge,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Schneidet die Bilddatei des Items zu und aktualisiert Größe sowie Thumbnail.
     * Legt beim ersten Zuschneiden eine unveränderte Kopie unter originalPath ab.
     *
     * @throws \InvalidArgumentException bei ungültigen Parametern oder nicht unterstütztem Typ
     */
    public function applyCrop(MediaItem $item, int $x, int $y, int $w, int $h, int $naturalW, int $naturalH): void
    {
        if ($item->getType() !== MediaItemType::Image) {
            throw new \InvalidArgumentException('Zuschneiden ist nur für Bilddateien möglich.');
        }

        $relativePath = $item->getPath();
        if ($relativePath === null || $relativePath === '') {
            throw new \InvalidArgumentException('Kein gültiger Dateipfad für dieses Medium.');
        }

        $absolutePath = $this->storageDir . '/' . $relativePath;
        if (!is_file($absolutePath) || !is_readable($absolutePath)) {
            throw new \InvalidArgumentException('Die Bilddatei konnte nicht gelesen werden.');
        }

        if ($w <= 0 || $h <= 0) {
            throw new \InvalidArgumentException('Breite und Höhe des Ausschnitts müssen größer als 0 sein.');
        }

        if ($x < 0 || $y < 0) {
            throw new \InvalidArgumentException('Ungültige Zuschnitt-Koordinaten.');
        }

        if ($naturalW <= 0 || $naturalH <= 0) {
            throw new \InvalidArgumentException('Ungültige Referenzabmessungen des Bildes.');
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($absolutePath);
        $imgW = $image->width();
        $imgH = $image->height();

        if (abs($imgW - $naturalW) > self::NATURAL_TOLERANCE || abs($imgH - $naturalH) > self::NATURAL_TOLERANCE) {
            throw new \InvalidArgumentException('Die gemeldeten Bildabmessungen stimmen nicht mit der Datei überein.');
        }

        $x = min($x, $imgW - 1);
        $y = min($y, $imgH - 1);
        $w = min($w, $imgW - $x);
        $h = min($h, $imgH - $y);

        if ($w <= 0 || $h <= 0) {
            throw new \InvalidArgumentException('Der Zuschnitt liegt außerhalb des Bildes.');
        }

        $this->ensureOriginalBackup($item, $relativePath, $absolutePath);

        $image->crop($w, $h, $x, $y);

        $extension = strtolower((string) $item->getExtension());
        try {
            $encoded = match ($extension) {
                'jpg', 'jpeg' => $image->toJpeg(quality: 82),
                'png' => $image->toPng(),
                'webp' => $image->toWebp(quality: 82),
                default => throw new \InvalidArgumentException('Dateiformat wird für Zuschnitt nicht unterstützt.'),
            };
        } catch (\Throwable $e) {
            $this->logger->error('Media crop: encoding failed.', ['exception' => $e, 'path' => $relativePath]);
            throw new \InvalidArgumentException('Das Bild konnte nicht gespeichert werden.');
        }

        $encoded->save($absolutePath);
        $item->setSizeBytes((int) filesize($absolutePath));

        $this->regenerateThumbnail($item, $absolutePath, $relativePath);
    }

    /**
     * Stellt die Hauptdatei aus dem gespeicherten Original wieder her und entfernt die Sicherungskopie.
     *
     * @throws \InvalidArgumentException
     */
    public function restoreOriginal(MediaItem $item): void
    {
        if ($item->getType() !== MediaItemType::Image) {
            throw new \InvalidArgumentException('Wiederherstellen ist nur für Bilddateien möglich.');
        }

        $originalRelative = $item->getOriginalPath();
        if ($originalRelative === null || $originalRelative === '') {
            throw new \InvalidArgumentException('Es liegt kein gespeichertes Original vor.');
        }

        $mainRelative = $item->getPath();
        if ($mainRelative === null || $mainRelative === '') {
            throw new \InvalidArgumentException('Kein gültiger Dateipfad für dieses Medium.');
        }

        $originalAbsolute = $this->storageDir . '/' . $originalRelative;
        $mainAbsolute = $this->storageDir . '/' . $mainRelative;

        if (!is_file($originalAbsolute) || !is_readable($originalAbsolute)) {
            throw new \InvalidArgumentException('Die Originaldatei fehlt oder ist nicht lesbar.');
        }

        if (!@copy($originalAbsolute, $mainAbsolute)) {
            $this->logger->error('Media restore: copy from original failed.', ['from' => $originalRelative, 'to' => $mainRelative]);
            throw new \InvalidArgumentException('Das Original konnte nicht wiederhergestellt werden.');
        }

        try {
            unlink($originalAbsolute);
        } catch (\Throwable $e) {
            $this->logger->error('Media restore: unlink backup failed.', ['exception' => $e, 'path' => $originalRelative]);
        }

        $item->setOriginalPath(null);
        $item->setSizeBytes((int) filesize($mainAbsolute));

        $this->regenerateThumbnail($item, $mainAbsolute, $mainRelative);
    }

    private function ensureOriginalBackup(MediaItem $item, string $relativePath, string $absolutePath): void
    {
        $existing = $item->getOriginalPath();
        if ($existing !== null && $existing !== '') {
            return;
        }

        $backupRelative = $this->buildOriginalBackupRelativePath($relativePath);
        $backupAbsolute = $this->storageDir . '/' . $backupRelative;

        $dir = dirname($backupAbsolute);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger->error('Media crop: backup directory failed.', ['dir' => $dir]);
            throw new \InvalidArgumentException('Sicherungskopie des Originals konnte nicht angelegt werden.');
        }

        if (!@copy($absolutePath, $backupAbsolute)) {
            $this->logger->error('Media crop: backup copy failed.', ['path' => $relativePath]);
            throw new \InvalidArgumentException('Sicherungskopie des Originals konnte nicht angelegt werden.');
        }

        $item->setOriginalPath($backupRelative);
    }

    private function buildOriginalBackupRelativePath(string $relativePath): string
    {
        $base = basename($relativePath);
        if (!preg_match('/^(.+)\.([^.]+)$/', $base, $m)) {
            throw new \InvalidArgumentException('Ungültiger Dateiname für Sicherungskopie.');
        }

        $dir = dirname($relativePath);

        return ($dir === '.' ? '' : $dir . '/') . $m[1] . '.original.' . $m[2];
    }

    private function regenerateThumbnail(MediaItem $item, string $absoluteItemPath, string $relativeItemPath): void
    {
        $thumbRelative = $item->getThumbnailPath();
        if ($thumbRelative === null || $thumbRelative === '') {
            $id = basename($relativeItemPath);
            $id = preg_replace('/\.[^.]+$/', '', $id) ?? $id;
            $thumbRelative = 'thumbnails/' . $id . '.jpg';
            $item->setThumbnailPath($thumbRelative);
        }

        $thumbAbsolute = $this->storageDir . '/' . $thumbRelative;
        $thumbDir = dirname($thumbAbsolute);
        if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
            $this->logger->error('Media crop: thumbnail directory failed.', ['dir' => $thumbDir]);

            return;
        }

        try {
            $manager = new ImageManager(new Driver());
            $thumbImage = $manager->read($absoluteItemPath);
            $thumbImage->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);
            $encoded = $thumbImage->toJpeg(quality: 82);
            $encoded->save($thumbAbsolute);
        } catch (\Throwable $e) {
            $this->logger->error('Media crop: thumbnail generation failed.', [
                'exception' => $e,
                'path' => $relativeItemPath,
            ]);
        }
    }
}
