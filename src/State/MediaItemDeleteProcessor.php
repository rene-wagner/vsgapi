<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\MediaItem;
use App\Service\Media\MediaDeleteService;

final class MediaItemDeleteProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly MediaDeleteService $mediaDeleteService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        if ($data instanceof MediaItem) {
            $this->mediaDeleteService->delete($data);
        }

        return null;
    }
}
