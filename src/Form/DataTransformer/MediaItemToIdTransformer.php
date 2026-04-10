<?php

namespace App\Form\DataTransformer;

use App\Entity\MediaItem;
use App\Repository\MediaItemRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @implements DataTransformerInterface<MediaItem|null, string>
 */
class MediaItemToIdTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly MediaItemRepository $mediaItemRepository,
    ) {
    }

    public function transform(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!$value instanceof MediaItem) {
            throw new TransformationFailedException('Expected a MediaItem instance.');
        }

        return (string) $value->getId();
    }

    public function reverseTransform(mixed $value): ?MediaItem
    {
        if ($value === null || $value === '') {
            return null;
        }

        $mediaItem = $this->mediaItemRepository->find((int) $value);

        if ($mediaItem === null) {
            throw new TransformationFailedException(sprintf('MediaItem with ID "%s" not found.', $value));
        }

        return $mediaItem;
    }
}
