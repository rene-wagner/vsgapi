<?php

namespace App\Repository;

use App\Entity\MediaFolder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaFolder>
 */
class MediaFolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaFolder::class);
    }

    /**
     * @return list<MediaFolder>
     */
    public function findRoots(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.parent IS NULL')
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
