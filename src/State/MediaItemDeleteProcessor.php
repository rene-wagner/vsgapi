<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaItem;
use App\Service\Media\MediaDeleteService;

/** @implements ProcessorInterface<MediaItem, null> */
final class MediaItemDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MediaDeleteService $mediaDeleteService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        $this->mediaDeleteService->delete($data);

        return null;
    }
}
