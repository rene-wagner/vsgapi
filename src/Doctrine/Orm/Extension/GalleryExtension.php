<?php

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class GalleryExtension implements QueryCollectionExtensionInterface
{
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== MediaItem::class) {
            return;
        }

        if (!$operation instanceof GetCollection || $operation->getName() !== 'gallery') {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere(sprintf('%s.type = :gallery_type', $rootAlias))
            ->setParameter('gallery_type', MediaItemType::Image)
            ->andWhere(sprintf('%s.isHiddenInApi = :gallery_hidden', $rootAlias))
            ->setParameter('gallery_hidden', false);

        $year = $context['filters']['year'] ?? null;
        if ($year === null || $year === '') {
            return;
        }

        if (!\is_scalar($year) || !preg_match('/^\\d{4}$/', (string) $year)) {
            throw new BadRequestHttpException('Der Parameter "year" muss eine vierstellige Jahreszahl sein.');
        }

        $year = (int) $year;
        if ($year < 1000 || $year > 9998) {
            throw new BadRequestHttpException('Der Parameter "year" muss zwischen 1000 und 9998 liegen.');
        }

        $queryBuilder
            ->andWhere(sprintf('%s.imageCreatedAt >= :gallery_year_start', $rootAlias))
            ->andWhere(sprintf('%s.imageCreatedAt < :gallery_year_end', $rootAlias))
            ->setParameter('gallery_year_start', new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year)))
            ->setParameter('gallery_year_end', new \DateTimeImmutable(sprintf('%d-01-01 00:00:00', $year + 1)));
    }
}
