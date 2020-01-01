<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserNotificationManager.
 */
class UserNotificationManager
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
     * @param User $user
     *
     * @return bool
     */
    public function add(
        string $type,
        array $data = [],
        User $user = null
    ) {
        if (!$user) {
            $user = $this->security->getUser();
        }

        $userNotification = new UserNotification();
        $userNotification
            ->setType($type)
            ->setData($data)
            ->setUser($user)
        ;

        $this->em->persist($userNotification);
        $this->em->flush();

        return true;
    }
}
