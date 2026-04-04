<?php

namespace App\Repository;

use App\Entity\MediaFolder;
use App\Entity\MediaItem;
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
}
