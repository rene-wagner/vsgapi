<?php

namespace App\Repository;

use App\Entity\ClubHistoryHallOfFameEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClubHistoryHallOfFameEntry>
 */
class ClubHistoryHallOfFameEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClubHistoryHallOfFameEntry::class);
    }
}
