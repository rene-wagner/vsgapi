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

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        if (!$object instanceof MediaItem) {
            throw new \InvalidArgumentException('Expected MediaItem.');
        }

        if (isset($context[self::ALREADY_CALLED])) {
            $normalized = $this->normalizer->normalize($object, $format, $context);

            return \is_array($normalized) ? $normalized : [];
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
        $data['size_human'] = $this->mediaUrlService->formatSizeHuman($object->getSizeBytes());
        $data['folder_id'] = $object->getFolder()?->getId();
        $data['category_id'] = $object->getCategory()?->getId();

        return $data;
    }

    /** @param array<string, mixed> $context */
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