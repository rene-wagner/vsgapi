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

        if ($type === MediaItemType::Image && $mimeType !== 'image/svg+xml') {
            $thumbRelative = 'thumbnails/' . $id . '.jpg';
            $thumbAbsolute = $this->storageDir . '/' . $thumbRelative;
            $thumbDir = dirname($thumbAbsolute);
            if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
                $this->logger->error('Thumbnail directory could not be created.', ['dir' => $thumbDir]);
            } else {
                try {
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($absolutePath);
                    $image->scaleDown(width: $this->thumbnailMaxEdge, height: $this->thumbnailMaxEdge);
                    $encoded = $image->toJpeg(quality: 82);
                    $encoded->save($thumbAbsolute);
                    $item->setThumbnailPath($thumbRelative);
                } catch (\Throwable $e) {
                    $this->logger->error('Media thumbnail generation failed.', [
                        'exception' => $e,
                        'path' => $relativePath,
                    ]);
                }
            }
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }
}
