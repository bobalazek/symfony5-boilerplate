<?php

namespace App\Repository\ORM;

use App\Entity\UserTfaEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserTfaEmail|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTfaEmail|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTfaEmail[]    findAll()
 * @method UserTfaEmail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTfaEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTfaEmail::class);
    }
}
