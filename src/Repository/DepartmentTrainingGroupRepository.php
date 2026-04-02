<?php

namespace App\Repository;

use App\Entity\DepartmentTrainingGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DepartmentTrainingGroup>
 */
class DepartmentTrainingGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DepartmentTrainingGroup::class);
    }
}
