<?php

namespace App\Repository;

use App\Entity\ClubHistoryMembershipStat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClubHistoryMembershipStat>
 */
class ClubHistoryMembershipStatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubHistoryMembershipStat::class);
    }
}
