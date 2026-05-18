<?php

namespace App\Repository;

use App\Entity\ClubHistoryMilestone;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClubHistoryMilestone>
 */
class ClubHistoryMilestoneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubHistoryMilestone::class);
    }
}
