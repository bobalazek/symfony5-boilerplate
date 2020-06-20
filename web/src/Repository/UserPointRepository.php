<?php

namespace App\Repository;

use App\Entity\UserPoint;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserPoint|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPoint|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPoint[]    findAll()
 * @method UserPoint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPointRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPoint::class);
    }
}
