<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Entity\UserPoint;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class UserManager.
 */
class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return bool
     */
    public function isFollowing(User $user, User $userFollowing)
    {
        return (bool) $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $userFollowing,
                'userFollowing' => $user,
            ])
        ;
    }

    /**
     * @return bool
     */
    public function isFollowedBy(User $user, User $userFollowedBy)
    {
        return (bool) $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $user,
                'userFollowing' => $userFollowing,
            ])
        ;
    }

    /**
     * @return bool
     */
    public function isBlocking(User $user, User $userBlocking)
    {
        return (bool) $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $userBlocking,
            ])
        ;
    }

    /**
     * @return bool
     */
    public function isBlockedBy(User $user, User $userBlockedBy)
    {
        return (bool) $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $userBlockedBy,
                'userBlocked' => $user,
            ])
        ;
    }

    /**
     * @return int
     */
    public function followersCount(User $user)
    {
        return count(
            $this->em
                ->getRepository(UserFollower::class)
                ->findBy([
                    'user' => $user,
                    'status' => UserFollower::STATUS_APPROVED,
                ])
        );
    }

    /**
     * @return int
     */
    public function followingCount(User $user)
    {
        return count(
            $this->em
                ->getRepository(UserFollower::class)
                ->findBy([
                    'userFollowing' => $user,
                    'status' => UserFollower::STATUS_APPROVED,
                ])
        );
    }

    /**
     * @return int
     */
    public function pointsCount(User $user)
    {
        return $this->em
            ->getRepository(UserPoint::class)
            ->createQueryBuilder('up')
            ->select('SUM(up.amount) as amount')
            ->where('up.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;
    }
}
