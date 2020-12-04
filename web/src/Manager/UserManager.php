<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Entity\UserPoint;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

/**
 * Class UserManager.
 */
class UserManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var UploadHandler
     */
    private $uploadHandler;

    public function __construct(
        EntityManagerInterface $em,
        Security $security,
        UploadHandler $uploadHandler
    ) {
        $this->em = $em;
        $this->security = $security;
        $this->uploadHandler = $uploadHandler;
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
    public function isFollowedBy(User $user, User $userFollowing)
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

    /**
     * @return bool
     */
    public function removeUploadedImage()
    {
        $userMyself = $this->getUser();
        if (!$userMyself) {
            return false;
        }

        if ($userMyself->getImageFileEmbedded()) {
            $this->uploadHandler->remove($userMyself, 'imageFile');
        }

        return true;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        /** @var string|\Stringable|User $user */
        $user = $this->security->getUser();
        if (!($user instanceof UserInterface)) {
            return null;
        }

        return $user;
    }
}
