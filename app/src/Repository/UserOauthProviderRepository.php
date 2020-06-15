<?php

namespace App\Repository;

use App\Entity\UserOauthProvider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserOauthProvider|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOauthProvider|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOauthProvider[]    findAll()
 * @method UserOauthProvider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserOauthProviderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserOauthProvider::class);
    }
}
