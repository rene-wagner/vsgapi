<?php

namespace App\Service\Media;

use App\Entity\Category;
use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

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
        private readonly MediaStorageService $mediaStorageService,
        private readonly MediaThumbnailService $mediaThumbnailService,
        private readonly ImageMetadataDateExtractor $imageMetadataDateExtractor,
        private readonly string $storageDir,
        private readonly int $maxUploadBytes,
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
        $uploadedAt = new \DateTimeImmutable();
        $originalName = $file->getClientOriginalName();
        $baseName = $displayName !== null && $displayName !== ''
            ? $displayName
            : pathinfo($originalName, PATHINFO_FILENAME) . '.' . $extension;

        $id = Uuid::v4()->toRfc4122();
        $filename = $this->mediaStorageService->buildMediaFilename(pathinfo($baseName, PATHINFO_FILENAME), $id, $extension);
        $relativePath = $this->mediaStorageService->buildItemRelativePath($folder, $filename);
        $absolutePath = $this->storageDir . '/' . $relativePath;

        $this->mediaStorageService->moveUploadedFile($file, $relativePath);

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

        if ($type === MediaItemType::Image) {
            $item->setImageCreatedAt($this->imageMetadataDateExtractor->extract($absolutePath, $mimeType) ?? $uploadedAt);
        }

        if ($type === MediaItemType::Image && !\in_array($mimeType, ['image/svg+xml', 'image/gif'], true)) {
            $thumbRelative = $this->mediaStorageService->buildThumbnailRelativePath($folder, basename($relativePath));
            if ($this->mediaThumbnailService->generate($relativePath, $thumbRelative)) {
                $item->setThumbnailPath($thumbRelative);
            }
        }

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $item;
    }
}
