<?php

namespace App\Repository\ORM;

use App\Entity\Thread;
use App\Entity\ThreadUser;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Thread|null find($id, $lockMode = null, $lockVersion = null)
 * @method Thread|null findOneBy(array $criteria, array $orderBy = null)
 * @method Thread[]    findAll()
 * @method Thread[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThreadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Thread::class);
    }

    public function getByIdAndUser(int $id, User $user)
    {
        return $this
            ->createQueryBuilder('t')
            ->leftJoin('t.threadUsers', 'tu')
            ->where('t.id = :id AND tu.user = :user')
            ->setParameter('id', $id)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function getByUserOneAndTwo(User $userOne, User $userTwo)
    {
        $entityManager = $this->getEntityManager();
        $threadUserRepository = $entityManager
            ->getRepository(ThreadUser::class)
        ;

        $subQueryOne = $threadUserRepository
            ->createQueryBuilder('tu1')
            ->where('tu1.thread = t AND tu1.user = :userOne')
        ;
        $subQueryTwo = $threadUserRepository
            ->createQueryBuilder('tu2')
            ->where('tu2.thread = t AND tu2.user = :userTwo')
        ;

        $queryBuilder = $this->createQueryBuilder('t');

        return $queryBuilder
            ->leftJoin('t.threadUsers', 'tu')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->exists($subQueryOne->getDQL()),
                $queryBuilder->expr()->exists($subQueryTwo->getDQL())
            ))
            ->setParameter('userOne', $userOne)
            ->setParameter('userTwo', $userTwo)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
