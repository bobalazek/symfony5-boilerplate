<?php

namespace App\Repository;

use App\Entity\UserTfaMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserTfaMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTfaMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTfaMethod[]    findAll()
 * @method UserTfaMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTfaMethodRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTfaMethod::class);
    }
}
