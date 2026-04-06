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
        return $this->findByParentOrdered(null);
    }

    /**
     * @return list<MediaFolder>
     */
    public function findByParentOrdered(?MediaFolder $parent): array
    {
        $qb = $this->createQueryBuilder('f')
            ->orderBy('f.name', 'ASC');

        if ($parent === null) {
            $qb->andWhere('f.parent IS NULL');
        } else {
            $qb->andWhere('f.parent = :parent')
                ->setParameter('parent', $parent);
        }

        return $qb->getQuery()->getResult();
    }
}
