<?php

namespace App\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\ORM\QueryBuilder;

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
    }
}