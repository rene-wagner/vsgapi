<?php

namespace App\Repository;

use App\Entity\MembershipApplication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MembershipApplication>
 */
class MembershipApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembershipApplication::class);
    }

    /**
     * @return list<MembershipApplication>
     */
    public function findAllOrderedByCreatedAtDesc(): array
    {
        return $this->createQueryBuilder('m')
            ->orderBy('m.lastName', 'ASC')
            ->addOrderBy('m.createdAt', 'DESC')
            ->addOrderBy('m.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
