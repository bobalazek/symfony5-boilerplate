<?php

namespace App\Repository\ORM;

use App\Entity\UserTfaRecoveryCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserTfaRecoveryCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserTfaRecoveryCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserTfaRecoveryCode[]    findAll()
 * @method UserTfaRecoveryCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserTfaRecoveryCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserTfaRecoveryCode::class);
    }
}
