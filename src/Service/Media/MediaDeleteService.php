<?php

namespace App\Service\Media;

use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class MediaDeleteService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly string $storageDir,
    ) {
    }

    public function delete(MediaItem $item): void
    {
        $this->removeFileIfExists($item->getPath());
        $this->removeFileIfExists($item->getThumbnailPath());

        $this->entityManager->remove($item);
        $this->entityManager->flush();
    }

    private function removeFileIfExists(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $full = $this->storageDir . '/' . $relativePath;
        if (!is_file($full)) {
            return;
        }

        try {
            unlink($full);
        } catch (\Throwable $e) {
            $this->logger->error('Media file delete failed.', [
                'exception' => $e,
                'path' => $relativePath,
            ]);
        }
    }
}
