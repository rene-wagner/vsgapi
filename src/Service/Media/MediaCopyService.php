<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaCopyService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        private readonly string $storageDir,
    ) {
    }

    public function copy(MediaItem $source, ?MediaFolder $targetFolder = null): MediaItem
    {
        $folder = $targetFolder ?? $source->getFolder();
        $newId = bin2hex(random_bytes(16));
        $ext = $source->getExtension() ?? '';
        $newRelative = 'items/' . $newId . '.' . $ext;
        $srcAbsolute = $this->storageDir . '/' . $source->getPath();
        $dstAbsolute = $this->storageDir . '/' . $newRelative;

        if (!is_file($srcAbsolute)) {
            throw new BadRequestHttpException('Quelldatei fehlt.');
        }

        $dir = dirname($dstAbsolute);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            $this->logger->error('Media copy: target directory failed.', ['dir' => $dir]);
            throw new BadRequestHttpException('Kopieren fehlgeschlagen.');
        }

        if (!@copy($srcAbsolute, $dstAbsolute)) {
            $this->logger->error('Media copy: file copy failed.', ['from' => $source->getPath()]);
            throw new BadRequestHttpException('Kopieren fehlgeschlagen.');
        }

        $newThumbRelative = null;
        $thumbSrc = $source->getThumbnailPath();
        if ($thumbSrc !== null && $thumbSrc !== '' && is_file($this->storageDir . '/' . $thumbSrc)) {
            $newThumbRelative = 'thumbnails/' . $newId . '.jpg';
            $thumbDst = $this->storageDir . '/' . $newThumbRelative;
            $thumbDir = dirname($thumbDst);
            if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true) && !is_dir($thumbDir)) {
                $this->logger->error('Media copy: thumbnail dir failed.', ['dir' => $thumbDir]);
            } elseif (!@copy($this->storageDir . '/' . $thumbSrc, $thumbDst)) {
                $this->logger->error('Media copy: thumbnail copy failed.');
                $newThumbRelative = null;
            }
        }

        $copy = new MediaItem();
        $copy->setFolder($folder);
        $copy->setCategory($source->getCategory());
        $copy->setName($source->getName() ?? 'copy');
        $copy->setOriginalFilename($source->getOriginalFilename() ?? '');
        $copy->setMimeType($source->getMimeType() ?? '');
        $copy->setExtension($ext);
        $itemType = $source->getType();
        if ($itemType === null) {
            throw new \LogicException('Source media item has no type.');
        }
        $copy->setType($itemType);
        $copy->setSizeBytes((int) filesize($dstAbsolute));
        $copy->setPath($newRelative);
        $copy->setThumbnailPath($newThumbRelative);
        $copy->setDescription($source->getDescription());

        $this->entityManager->persist($copy);
        $this->entityManager->flush();

        return $copy;
    }
}
