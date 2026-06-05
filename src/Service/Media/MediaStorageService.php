<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\String\Slugger\AsciiSlugger;

class MediaStorageService
{
    private const THUMBNAIL_DIR = '_thumbnails';

    private AsciiSlugger $slugger;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $storageDir,
    ) {
        $this->slugger = new AsciiSlugger('de');
    }

    public function buildMediaFilename(string $name, string $id, string $extension): string
    {
        $slug = $this->slug($name);

        return $slug . '-' . $id . '.' . $extension;
    }

    public function buildItemRelativePath(?MediaFolder $folder, string $filename): string
    {
        $folderPath = $this->buildFolderPath($folder);

        if ($folderPath === '') {
            return $filename;
        }

        return $folderPath . '/' . $filename;
    }

    public function buildThumbnailRelativePath(?MediaFolder $folder, string $filename): string
    {
        $folderPath = $this->buildFolderPath($folder);
        $thumbnailFilename = pathinfo($filename, PATHINFO_FILENAME) . '.jpg';

        if ($folderPath === '') {
            return self::THUMBNAIL_DIR . '/' . $thumbnailFilename;
        }

        return self::THUMBNAIL_DIR . '/' . $folderPath . '/' . $thumbnailFilename;
    }

    public function moveUploadedFile(UploadedFile $file, string $relativePath): void
    {
        $absolutePath = $this->absolutePath($relativePath);
        $this->ensureDirectoryExists(dirname($absolutePath));

        try {
            $file->move(dirname($absolutePath), basename($absolutePath));
        } catch (\Throwable $e) {
            $this->logger->error('Media file move failed.', ['exception' => $e]);
            throw new BadRequestHttpException('Speichern fehlgeschlagen.');
        }
    }

    public function copyFile(string $sourceRelativePath, string $targetRelativePath): void
    {
        $sourceAbsolutePath = $this->absolutePath($sourceRelativePath);
        $targetAbsolutePath = $this->absolutePath($targetRelativePath);

        if (!is_file($sourceAbsolutePath)) {
            throw new BadRequestHttpException('Quelldatei fehlt.');
        }

        $this->ensureDirectoryExists(dirname($targetAbsolutePath));

        if (!@copy($sourceAbsolutePath, $targetAbsolutePath)) {
            $this->logger->error('Media file copy failed.', ['from' => $sourceRelativePath, 'to' => $targetRelativePath]);
            throw new BadRequestHttpException('Kopieren fehlgeschlagen.');
        }
    }

    public function moveStorageFile(string $sourceRelativePath, string $targetRelativePath, bool $required = true): void
    {
        $this->moveFile($sourceRelativePath, $targetRelativePath, $required);
    }

    public function ensureFolderExists(MediaFolder $folder): void
    {
        $this->ensureDirectoryExists($this->absolutePath($this->buildFolderPath($folder)));
    }

    /**
     * @return array{folders: list<MediaFolder>, items: list<MediaItem>}
     */
    public function syncFolder(MediaFolder $folder, ?string $oldPath): array
    {
        $newPath = $this->buildFolderPath($folder);
        $oldPath = $oldPath !== null ? trim($oldPath, '/') : null;

        if ($oldPath === null || $oldPath === '') {
            $this->ensureDirectoryExists($this->absolutePath($newPath));
        } elseif ($oldPath !== $newPath) {
            $this->moveDirectory($oldPath, $newPath);
            $this->moveDirectory(self::THUMBNAIL_DIR . '/' . $oldPath, self::THUMBNAIL_DIR . '/' . $newPath, false);
        } else {
            $this->ensureDirectoryExists($this->absolutePath($newPath));
        }

        $changedFolders = [];
        $changedItems = [];
        $this->updateFolderSubtreePaths($folder, $oldPath, $changedFolders, $changedItems);

        return ['folders' => $changedFolders, 'items' => $changedItems];
    }

    public function syncItemLocation(MediaItem $item, ?string $oldPath, ?string $oldThumbnailPath): void
    {
        if ($oldPath === null || $oldPath === '') {
            return;
        }

        $oldPath = trim($oldPath, '/');
        $newPath = $this->buildItemRelativePath($item->getFolder(), basename($oldPath));

        if ($oldPath !== $newPath) {
            $this->moveFile($oldPath, $newPath);
            $item->setPath($newPath);
        }

        if ($oldThumbnailPath === null || $oldThumbnailPath === '') {
            return;
        }

        $oldThumbnailPath = trim($oldThumbnailPath, '/');
        $newThumbnailPath = $this->buildThumbnailRelativePath($item->getFolder(), basename($newPath));

        if ($oldThumbnailPath !== $newThumbnailPath) {
            $this->moveFile($oldThumbnailPath, $newThumbnailPath, false);
            $item->setThumbnailPath($newThumbnailPath);
        }
    }

    public function absolutePath(string $relativePath): string
    {
        $relativePath = trim($relativePath, '/');

        if ($relativePath === '') {
            return rtrim($this->storageDir, '/');
        }

        return rtrim($this->storageDir, '/') . '/' . $relativePath;
    }

    public function buildFolderPath(?MediaFolder $folder): string
    {
        if ($folder === null) {
            return '';
        }

        $segments = [];
        $current = $folder;
        $guard = 0;

        while ($current !== null && $guard < 100) {
            array_unshift($segments, $this->slug((string) $current->getName()));
            $current = $current->getParent();
            ++$guard;
        }

        return implode('/', $segments);
    }

    /**
     * @param list<MediaFolder> $changedFolders
     * @param list<MediaItem> $changedItems
     */
    private function updateFolderSubtreePaths(MediaFolder $folder, ?string $oldPath, array &$changedFolders, array &$changedItems): void
    {
        $newPath = $this->buildFolderPath($folder);
        $currentOldPath = $folder->getStoragePath();
        $folder->setStoragePath($newPath);
        $changedFolders[] = $folder;

        foreach ($folder->getMediaItems() as $item) {
            $path = $item->getPath();
            if ($path !== null && $path !== '') {
                $item->setPath($this->replaceFolderPrefix($path, $oldPath ?? $currentOldPath, $newPath));
            }

            $thumbnailPath = $item->getThumbnailPath();
            if ($thumbnailPath !== null && $thumbnailPath !== '') {
                $item->setThumbnailPath($this->replaceFolderPrefix($thumbnailPath, self::THUMBNAIL_DIR . '/' . ($oldPath ?? $currentOldPath), self::THUMBNAIL_DIR . '/' . $newPath));
            }

            $changedItems[] = $item;
        }

        foreach ($folder->getChildren() as $child) {
            $this->updateFolderSubtreePaths($child, $child->getStoragePath(), $changedFolders, $changedItems);
        }
    }

    private function replaceFolderPrefix(string $path, ?string $oldPrefix, string $newPrefix): string
    {
        $path = trim($path, '/');
        $oldPrefix = $oldPrefix !== null ? trim($oldPrefix, '/') : '';
        $newPrefix = trim($newPrefix, '/');
        $basename = basename($path);

        if ($oldPrefix !== '' && str_starts_with($path, $oldPrefix . '/')) {
            return $newPrefix . substr($path, strlen($oldPrefix));
        }

        if ($newPrefix === '') {
            return $basename;
        }

        return $newPrefix . '/' . $basename;
    }

    private function moveFile(string $oldPath, string $newPath, bool $required = true): void
    {
        $oldAbsolutePath = $this->absolutePath($oldPath);
        $newAbsolutePath = $this->absolutePath($newPath);

        if (!is_file($oldAbsolutePath)) {
            if ($required) {
                throw new BadRequestHttpException('Die Quelldatei fehlt.');
            }

            return;
        }

        $this->ensureDirectoryExists(dirname($newAbsolutePath));

        if (is_file($newAbsolutePath)) {
            throw new BadRequestHttpException('Im Zielordner existiert bereits eine Datei mit diesem Namen.');
        }

        if (!rename($oldAbsolutePath, $newAbsolutePath)) {
            $this->logger->error('Media file rename failed.', ['from' => $oldPath, 'to' => $newPath]);
            throw new BadRequestHttpException('Verschieben fehlgeschlagen.');
        }
    }

    private function moveDirectory(string $oldPath, string $newPath, bool $createIfMissing = true): void
    {
        $oldAbsolutePath = $this->absolutePath($oldPath);
        $newAbsolutePath = $this->absolutePath($newPath);

        if (!is_dir($oldAbsolutePath)) {
            if ($createIfMissing) {
                $this->ensureDirectoryExists($newAbsolutePath);
            }

            return;
        }

        $this->ensureDirectoryExists(dirname($newAbsolutePath));

        if (is_dir($newAbsolutePath)) {
            throw new BadRequestHttpException('Im Zielordner existiert bereits ein Ordner mit diesem Namen.');
        }

        if (!rename($oldAbsolutePath, $newAbsolutePath)) {
            $this->logger->error('Media directory rename failed.', ['from' => $oldPath, 'to' => $newPath]);
            throw new BadRequestHttpException('Ordner konnte nicht verschoben werden.');
        }
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (is_dir($directory)) {
            return;
        }

        if (!mkdir($directory, 0775, true) && !is_dir($directory)) {
            $this->logger->error('Media storage directory could not be created.', ['dir' => $directory]);
            throw new BadRequestHttpException('Speichern fehlgeschlagen.');
        }
    }

    private function slug(string $value): string
    {
        $slug = strtolower($this->slugger->slug($value)->toString());

        return $slug !== '' ? $slug : 'ordner';
    }
}
