<?php

namespace App\Repository\ORM;

use App\Entity\UserFollower;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserFollower|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserFollower|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserFollower[]    findAll()
 * @method UserFollower[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserFollowerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserFollower::class);
    }
}
