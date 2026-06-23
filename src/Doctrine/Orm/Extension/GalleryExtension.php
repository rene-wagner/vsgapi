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
use Symfony\Component\String\Slugger\SluggerInterface;

final class GalleryExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly SluggerInterface $slugger)
    {
    }

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
            ->setParameter('gallery_hidden', false)
            ->addOrderBy(sprintf('%s.imageCreatedAt', $rootAlias), 'DESC')
            ->addOrderBy(sprintf('%s.createdAt', $rootAlias), 'DESC')
            ->addOrderBy(sprintf('%s.id', $rootAlias), 'DESC');

        $search = $context['filters']['q'] ?? null;
        if ($search !== null && $search !== '') {
            if (!\is_scalar($search)) {
                throw new BadRequestHttpException('Der Parameter "q" muss ein Suchbegriff sein.');
            }

            $search = trim((string) $search);
            if (mb_strlen($search) > 255) {
                throw new BadRequestHttpException('Der Parameter "q" darf maximal 255 Zeichen lang sein.');
            }

            if ($search !== '') {
                $folderAlias = $queryNameGenerator->generateJoinAlias('gallery_folder');
                $normalizedSearch = strtolower($this->slugger->slug($search)->toString());

                $queryBuilder
                    ->innerJoin(sprintf('%s.folder', $rootAlias), $folderAlias)
                    ->andWhere(sprintf('LOWER(%s.storagePath) LIKE :gallery_search', $folderAlias))
                    ->setParameter('gallery_search', '%' . addcslashes($normalizedSearch, '%_') . '%');
            }
        }

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
