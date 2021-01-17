<?php

namespace App\Repository;

use App\Entity\ThreadUserMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThreadUserMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThreadUserMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThreadUserMessage[]    findAll()
 * @method ThreadUserMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadUserMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadUserMessage::class);
    }
}
