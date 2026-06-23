<?php

namespace App\Repository;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaItem>
 */
class MediaItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaItem::class);
    }

    /**
     * @return list<MediaItem>
     */
    public function findByFolderOrdered(?MediaFolder $folder): array
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.name', 'ASC');

        if ($folder === null) {
            $qb->andWhere('m.folder IS NULL');
        } else {
            $qb->andWhere('m.folder = :folder')
                ->setParameter('folder', $folder);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return list<array{year: int, image_count: int}>
     */
    public function countPublicGalleryImagesByYear(): array
    {
        $rows = $this->getEntityManager()->getConnection()->executeQuery(
            'SELECT YEAR(image_created_at) AS year, COUNT(*) AS image_count
            FROM media_item
            WHERE type = :type
                AND is_hidden_in_api = 0
                AND image_created_at IS NOT NULL
            GROUP BY YEAR(image_created_at)
            ORDER BY year DESC',
            ['type' => MediaItemType::Image->value],
        )->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'year' => (int) $row['year'],
            'image_count' => (int) $row['image_count'],
        ], $rows);
    }
}
