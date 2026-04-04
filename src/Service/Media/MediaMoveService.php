<?php

namespace App\Service\Media;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use Doctrine\ORM\EntityManagerInterface;

class MediaMoveService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function move(MediaItem $item, ?MediaFolder $folder): void
    {
        $item->setFolder($folder);
        $this->entityManager->flush();
    }
}
