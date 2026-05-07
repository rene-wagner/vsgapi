<?php

namespace App\Service\Media;

use App\Entity\Category;
use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaUploadService
{
    /** @var array<string, array{0: string, 1: MediaItemType}> */
    private const ALLOWED_MIMES = [
        'image/jpeg' => ['jpg', MediaItemType::Image],
        'image/png' => ['png', MediaItemType::Image],
        'image/gif' => ['gif', MediaItemType::Image],
        'image/webp' => ['webp', MediaItemType::Image],
        'image/svg+xml' => ['svg', MediaItemType::Image],
        'application/pdf' => ['pdf', MediaItemType::Pdf],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly SvgSanitizerService $svgSanitizer,
        private readonly string $storageDir,
        private readonly int $maxUploadBytes,
        private readonly int $thumbnailMaxEdge,
    ) {
    }

    public function upload(
        UploadedFile $file,
        ?MediaFolder $folder = null,
        ?Category $category = null,
        ?string $description = null,
        ?string $displayName = null,
    ): MediaItem {
        if ($file->getSize() > $this->maxUploadBytes) {
            throw new BadRequestHttpException('Die Datei ist zu groß.');
        }

        $mimeType = (string) $file->getMimeType();
        if ($mimeType === '' || !isset(self::ALLOWED_MIMES[$mimeType])) {
            throw new BadRequestHttpException('Dateityp nicht erlaubt.');
        }

        [$extension, $type] = self::ALLOWED_MIMES[$mimeType];
        $originalName = $file->getClientOriginalName();
        $baseName = $displayName !== null && $displayName !== ''
            ? $displayName
            : pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;

        $id = bin2hex(random_bytes(16));
        $relativePath = 'items/' . $id . '.' . $extension;
        $absolutePath = $this->storageDir . '/' . $relativePath;

        $dir = dirname($absolutePath);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger->error('Media storage directory could not be created.', ['dir' => $dir]);
            throw new BadRequestHttpException('Speichern fehlgeschlagen.');
        }

        try {
            $file->move(dirname($absolutePath), basename($absolutePath));
        } catch (\Throwable $e) {
            $this->logger->error('Media file move failed.', ['exception' => $e]);
            throw new BadRequestHttpException('Speichern fehlgeschlagen.');
        }

        if ($mimeType === 'image/svg+xml') {
            $svgContent = file_get_contents($absolutePath);
            if ($svgContent === false) {
                $this->logger->error('SVG file could not be read for sanitization.', ['path' => $relativePath]);
                throw new BadRequestHttpException('Speichern fehlgeschlagen.');
            }
            $cleanSvg = $this->svgSanitizer->sanitize($svgContent);
            file_put_contents($absolutePath, $cleanSvg);
        }

        $item = new MediaItem();
        $item->setFolder($folder);
        $item->setCategory($category);
        $item->setName($baseName);
        $item->setOriginalFilename($originalName);
        $item->setMimeType($mimeType);
        $item->setExtension($extension);
        $item->setType($type);
        $item->setSizeBytes((int) filesize($absolutePath));
        $item->setPath($relativePath);
        $item->setDescription($description);

        if ($type === MediaItemType::Image && !\in_array($mimeType, ['image/svg+xml', 'image/gif'], true)) {
            $thumbRelative = $this->buildThumbnailRelativePath($relativePath);
            if ($this->generateThumbnail($relativePath, $thumbRelative)) {
                $item->setThumbnailPath($thumbRelative);
            }
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }

    public function regenerateThumbnail(MediaItem $item): bool
    {
        if ($item->getType() !== MediaItemType::Image || \in_array($item->getMimeType(), ['image/svg+xml', 'image/gif'], true)) {
            throw new BadRequestHttpException('Für dieses Medium kann kein Thumbnail erzeugt werden.');
        }

        $relativePath = $item->getPath();
        if ($relativePath === null || $relativePath === '') {
            throw new BadRequestHttpException('Die Quelldatei fehlt.');
        }
        $relativePath = $this->normalizeStorageRelativePath($relativePath);

        $previousThumbRelative = $item->getThumbnailPath();
        $thumbRelative = $this->buildThumbnailRelativePath($relativePath);

        if (!$this->generateThumbnail($relativePath, $thumbRelative)) {
            throw new BadRequestHttpException('Thumbnail konnte nicht erzeugt werden.');
        }

        if ($previousThumbRelative !== null && $previousThumbRelative !== '') {
            $previousThumbRelative = $this->normalizeStorageRelativePath($previousThumbRelative);
            if ($previousThumbRelative !== $thumbRelative) {
                $this->removeFileIfExists($previousThumbRelative);
            }
        }

        $item->setThumbnailPath($thumbRelative);

        return true;
    }

    private function generateThumbnail(string $relativePath, string $thumbRelative): bool
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

    private function buildThumbnailRelativePath(string $relativePath): string
    {
        $relativePath = $this->normalizeStorageRelativePath($relativePath);

        return 'thumbnails/' . pathinfo($relativePath, PATHINFO_FILENAME) . '.jpg';
    }

    private function normalizeStorageRelativePath(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            $path = substr($path, strlen('uploads/'));
        }

        return $path;
    }

    private function removeFileIfExists(string $relativePath): void
    {
        $absolutePath = $this->storageDir . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            return;
        }

        try {
            unlink($absolutePath);
        } catch (\Throwable $e) {
            $this->logger->error('Thumbnail delete failed.', [
                'exception' => $e,
                'path' => $relativePath,
            ]);
        }
    }
}
