<?php

namespace App\Repository;

use App\Entity\UserExport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserExport|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserExport|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserExport[]    findAll()
 * @method UserExport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserExportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserExport::class);
    }
}
