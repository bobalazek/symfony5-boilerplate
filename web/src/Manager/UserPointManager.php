<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserPoint;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserPointManager.
 */
class UserPointManager
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        Security $security,
        EntityManagerInterface $em
    ) {
        $this->security = $security;
        $this->em = $em;
    }

    /**
     * @return UserPoint
     */
    public function add(
        string $key,
        int $amount,
        array $data = [],
        User $user = null
    ) {
        if (!$user) {
            $user = $this->security->getUser();
        }

        $userPoint = new UserPoint();
        $userPoint
            ->setKey($key)
            ->setAmount($amount)
            ->setData($data)
            ->setUser($user)
        ;

        $this->em->persist($userPoint);
        $this->em->flush();

        return $userPoint;
    }
}
