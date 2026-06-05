<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use App\Repository\CategoryRepository;
use App\Repository\MediaFolderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Uid\Uuid;

class MediaFolderImportService
{
    /** @var array<string, int> */
    private const CATEGORY_IDS_BY_PATH_KEYWORD = [
        'badminton' => 2,
        'gymnastik' => 3,
        'tischtennis' => 4,
        'volleyball' => 5,
        'vereinsfest' => 1,
        'versammlung' => 1,
    ];

    /** @var array<string, array{0: string, 1: string}> */
    private const ALLOWED_EXTENSIONS = [
        'jpg' => ['image/jpeg', 'jpg'],
        'jpeg' => ['image/jpeg', 'jpg'],
        'png' => ['image/png', 'png'],
        'webp' => ['image/webp', 'webp'],
        'gif' => ['image/gif', 'gif'],
        'svg' => ['image/svg+xml', 'svg'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoryRepository $categoryRepository,
        private readonly MediaFolderRepository $mediaFolderRepository,
        private readonly MediaStorageService $mediaStorageService,
        private readonly MediaThumbnailService $mediaThumbnailService,
        private readonly SvgSanitizerService $svgSanitizer,
    ) {
    }

    /**
     * @return array{folders: list<string>, files: list<string>, skipped: list<string>}
     */
    public function import(string $sourceDirectory, bool $apply): array
    {
        $sourceDirectory = rtrim($sourceDirectory, '/');
        if (!is_dir($sourceDirectory) || !is_readable($sourceDirectory)) {
            throw new BadRequestHttpException('Der angegebene Ordner konnte nicht gelesen werden.');
        }

        $createdFolders = [];
        $importedFiles = [];
        $skippedFiles = [];
        $folderCache = [];

        foreach ($this->findDirectories($sourceDirectory) as $relativeDirectory) {
            $folder = $this->resolveFolder($relativeDirectory, $folderCache, $apply, $createdFolders);
            if ($apply) {
                $this->mediaStorageService->ensureFolderExists($folder);
            }
        }

        foreach ($this->findFiles($sourceDirectory) as $file) {
            $relativePath = $this->normalizeRelativePath(substr($file->getPathname(), strlen($sourceDirectory)));
            $fileInfo = $this->getAllowedFileInfo($file->getPathname());
            if ($fileInfo === null) {
                $skippedFiles[] = $relativePath;
                continue;
            }

            $relativeDirectory = $this->normalizeRelativePath($file->getRelativePath());
            $folder = $relativeDirectory !== ''
                ? $this->resolveFolder($relativeDirectory, $folderCache, $apply, $createdFolders)
                : null;

            [$mimeType, $extension] = $fileInfo;
            $originalFilename = $file->getFilename();
            $displayName = pathinfo($originalFilename, PATHINFO_FILENAME) . '.' . $extension;
            $id = Uuid::v4()->toRfc4122();
            $filename = $this->mediaStorageService->buildMediaFilename(pathinfo($displayName, PATHINFO_FILENAME), $id, $extension);
            $targetPath = $this->mediaStorageService->buildItemRelativePath($folder, $filename);
            $categoryId = $this->matchCategoryId($relativePath);

            $importedFiles[] = $relativePath . ' -> ' . $targetPath . ($categoryId !== null ? ' [Kategorie: ' . $categoryId . ']' : '');

            if (!$apply) {
                continue;
            }

            $this->mediaStorageService->copyLocalFileToStorage($file->getPathname(), $targetPath);

            if ($mimeType === 'image/svg+xml') {
                $this->sanitizeSvg($targetPath);
            }

            $item = new MediaItem();
            $item->setFolder($folder);
            $item->setCategory($categoryId !== null ? $this->categoryRepository->find($categoryId) : null);
            $item->setName($displayName);
            $item->setOriginalFilename($originalFilename);
            $item->setMimeType($mimeType);
            $item->setExtension($extension);
            $item->setType(MediaItemType::Image);
            $item->setSizeBytes((int) filesize($this->mediaStorageService->absolutePath($targetPath)));
            $item->setPath($targetPath);
            $item->setIsHiddenInApi(false);

            if ($this->shouldGenerateThumbnail($mimeType)) {
                $thumbnailPath = $this->mediaStorageService->buildThumbnailRelativePath($folder, basename($targetPath));
                if ($this->mediaThumbnailService->generate($targetPath, $thumbnailPath)) {
                    $item->setThumbnailPath($thumbnailPath);
                }
            }

            $this->entityManager->persist($item);
        }

        if ($apply) {
            $this->entityManager->flush();
        }

        return [
            'folders' => $createdFolders,
            'files' => $importedFiles,
            'skipped' => $skippedFiles,
        ];
    }

    /**
     * @return list<string>
     */
    private function findDirectories(string $sourceDirectory): array
    {
        $finder = new Finder();
        $finder->directories()->in($sourceDirectory)->sortByName();

        $directories = [];
        foreach ($finder as $directory) {
            $directories[] = $this->normalizeRelativePath($directory->getRelativePathname());
        }

        usort($directories, static fn (string $a, string $b): int => substr_count($a, '/') <=> substr_count($b, '/'));

        return $directories;
    }

    /**
     * @return iterable<\SplFileInfo>
     */
    private function findFiles(string $sourceDirectory): iterable
    {
        $finder = new Finder();
        $finder->files()->in($sourceDirectory)->sortByName();

        return $finder;
    }

    /**
     * @param array<string, MediaFolder> $folderCache
     * @param list<string> $createdFolders
     */
    private function resolveFolder(string $relativeDirectory, array &$folderCache, bool $apply, array &$createdFolders): MediaFolder
    {
        $relativeDirectory = $this->normalizeRelativePath($relativeDirectory);
        if (isset($folderCache[$relativeDirectory])) {
            return $folderCache[$relativeDirectory];
        }

        $parent = null;
        $segments = explode('/', $relativeDirectory);
        $path = '';

        foreach ($segments as $segment) {
            $path = $path === '' ? $segment : $path . '/' . $segment;
            if (isset($folderCache[$path])) {
                $parent = $folderCache[$path];
                continue;
            }

            $folder = $this->findExistingFolder($segment, $parent);
            if ($folder === null) {
                $folder = new MediaFolder();
                $folder->setName($segment);
                $folder->setParent($parent);
                $folder->setStoragePath($this->mediaStorageService->buildFolderPath($folder));
                $createdFolders[] = $path;

                if ($apply) {
                    $this->entityManager->persist($folder);
                }
            }

            $folderCache[$path] = $folder;
            $parent = $folder;
        }

        return $folderCache[$relativeDirectory];
    }

    private function findExistingFolder(string $name, ?MediaFolder $parent): ?MediaFolder
    {
        return $this->mediaFolderRepository->findOneBy([
            'name' => $name,
            'parent' => $parent,
        ]);
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function getAllowedFileInfo(string $path): ?array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!isset(self::ALLOWED_EXTENSIONS[$extension])) {
            return null;
        }

        return self::ALLOWED_EXTENSIONS[$extension];
    }

    private function shouldGenerateThumbnail(string $mimeType): bool
    {
        return \in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function matchCategoryId(string $relativePath): ?int
    {
        $relativePath = strtolower($relativePath);

        foreach (self::CATEGORY_IDS_BY_PATH_KEYWORD as $keyword => $categoryId) {
            if (str_contains($relativePath, $keyword)) {
                return $categoryId;
            }
        }

        return null;
    }

    private function sanitizeSvg(string $relativePath): void
    {
        $absolutePath = $this->mediaStorageService->absolutePath($relativePath);
        $svgContent = file_get_contents($absolutePath);
        if ($svgContent === false) {
            throw new BadRequestHttpException('SVG-Datei konnte nicht gelesen werden.');
        }

        file_put_contents($absolutePath, $this->svgSanitizer->sanitize($svgContent));
    }

    private function normalizeRelativePath(string $path): string
    {
        return trim(str_replace('\\', '/', $path), '/');
    }
}
