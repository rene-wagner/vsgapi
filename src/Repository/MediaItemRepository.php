<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\MediaFolder;
use App\Entity\MediaItem;
use App\Enum\MediaItemType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * @return Paginator<MediaItem>
     */
    public function findGalleryPaginated(int $page, int $perPage, ?Category $category = null): Paginator
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.type = :type')
            ->setParameter('type', MediaItemType::Image)
            ->andWhere('m.isHiddenInApi = false')
            ->orderBy('m.createdAt', 'DESC');

        if ($category !== null) {
            $qb->andWhere('m.category = :category')
                ->setParameter('category', $category);
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery(), true);
    }
}
