<?php

namespace App\EventSubscriber;

use App\Entity\MediaItem;
use App\Service\Media\MediaCropService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;

final class MediaItemFileSyncSubscriber
{
    public function __construct(
        private readonly MediaCropService $mediaCropService,
    ) {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->sync($args->getObject());
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->sync($args->getObject());
    }

    private function sync(object $object): void
    {
        if (!$object instanceof MediaItem) {
            return;
        }

        $this->mediaCropService->sync($object);
    }
}
