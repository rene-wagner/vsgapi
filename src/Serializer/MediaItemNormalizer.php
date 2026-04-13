<?php

namespace App\Serializer;

use App\Entity\MediaItem;
use App\Service\Media\MediaUrlService;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class MediaItemNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'media_item_normalizer.already_called';

    public function __construct(
        private readonly MediaUrlService $mediaUrlService,
    ) {
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof MediaItem) {
            throw new \InvalidArgumentException('Expected MediaItem.');
        }

        if (isset($context[self::ALREADY_CALLED])) {
            return $this->normalizer->normalize($object, $format, $context);
        }

        $context[self::ALREADY_CALLED] = true;

        try {
            $data = $this->normalizer->normalize($object, $format, $context);
        } finally {
            unset($context[self::ALREADY_CALLED]);
        }

        if (!\is_array($data)) {
            return [];
        }

        $data['original_url'] = $this->mediaUrlService->buildOriginalUrl($object);
        $data['thumbnail_url'] = $this->mediaUrlService->buildThumbnailUrl($object);
        $data['cropped_url'] = $this->mediaUrlService->buildCroppedUrl($object);
        $data['cropped_thumbnail_url'] = $this->mediaUrlService->buildCroppedThumbnailUrl($object);
        $data['display_url'] = $this->mediaUrlService->buildDisplayUrl($object);
        $data['url'] = $data['display_url'];
        $data['size_human'] = $this->mediaUrlService->formatSizeHuman($object->getSizeBytes());
        $data['folder_id'] = $object->getFolder()?->getId();
        $data['category_id'] = $object->getCategory()?->getId();

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!$data instanceof MediaItem || isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        $groups = $context['groups'] ?? [];

        return \is_array($groups) && \in_array('media_item:read', $groups, true);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            MediaItem::class => false,
        ];
    }
}