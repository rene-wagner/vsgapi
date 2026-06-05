<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Repository\MediaFolderRepository;

class MediaFolderChoiceBuilder
{
    public function __construct(private readonly MediaFolderRepository $mediaFolderRepository)
    {
    }

    /**
     * @return list<MediaFolder>
     */
    public function buildIndentedChoices(?int $excludedFolderId = null): array
    {
        $folders = $this->mediaFolderRepository->findAll();
        $childrenByParentId = [];

        foreach ($folders as $folder) {
            if ($excludedFolderId !== null && $this->isInExcludedBranch($folder, $excludedFolderId)) {
                continue;
            }

            $parentId = $folder->getParent()?->getId();
            $childrenByParentId[$parentId ?? 0][] = $folder;
        }

        foreach ($childrenByParentId as &$children) {
            usort($children, static fn (MediaFolder $left, MediaFolder $right): int => strcasecmp((string) $left->getName(), (string) $right->getName()));
        }
        unset($children);

        $choices = [];
        $this->appendChoices($choices, $childrenByParentId, null);

        return $choices;
    }

    public function buildIndentedLabel(MediaFolder $folder): string
    {
        $depth = 0;
        $current = $folder->getParent();
        $guard = 0;

        while ($current !== null && $guard < 100) {
            ++$depth;
            $current = $current->getParent();
            ++$guard;
        }

        if ($depth === 0) {
            return (string) $folder->getName();
        }

        return str_repeat('— ', $depth) . $folder->getName();
    }

    /**
     * @param array<int, list<MediaFolder>> $childrenByParentId
     * @param list<MediaFolder> $choices
     */
    private function appendChoices(array &$choices, array $childrenByParentId, ?int $parentId): void
    {
        foreach ($childrenByParentId[$parentId ?? 0] ?? [] as $folder) {
            $choices[] = $folder;
            $this->appendChoices($choices, $childrenByParentId, $folder->getId());
        }
    }

    private function isInExcludedBranch(MediaFolder $folder, int $excludedFolderId): bool
    {
        $current = $folder;
        $guard = 0;

        while ($current !== null && $guard < 100) {
            if ($current->getId() === $excludedFolderId) {
                return true;
            }

            $current = $current->getParent();
            ++$guard;
        }

        return false;
    }
}
