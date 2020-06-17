<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserTfaMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserTfaManager.
 */
class UserTfaManager
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
     * @return false|string
     */
    public function getDefaultMethod(User $user = null)
    {
        if (!$user) {
            /** @var User|null */
            $user = $this->security->getUser();
        }

        if (!$user) {
            return false;
        }

        if (!$user->isTfaEnabled()) {
            return false;
        }

        $methods = $this->getAvailableMethods($user);
        if (!$methods) {
            return false;
        }

        if (in_array(UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR, $methods)) {
            return UserTfaMethod::METHOD_GOOGLE_AUTHENTICATOR;
        }

        if (in_array(UserTfaMethod::METHOD_EMAIL, $methods)) {
            return UserTfaMethod::METHOD_EMAIL;
        }

        if (in_array(UserTfaMethod::METHOD_RECOVERY_CODES, $methods)) {
            return UserTfaMethod::METHOD_RECOVERY_CODES;
        }

        return false;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    public function getAvailableMethods(User $user = null)
    {
        if (!$user) {
            /** @var User|null */
            $user = $this->security->getUser();
        }

        if (!$user) {
            return [];
        }

        if (!$user->isTfaEnabled()) {
            return [];
        }

        $methods = [];
        $userTfaMethods = $user->getUserTfaMethods();
        foreach ($userTfaMethods as $userTfaMethod) {
            if (!$userTfaMethod->isEnabled()) {
                continue;
            }

            $methods[] = $userTfaMethod->getMethod();
        }

        return $methods;
    }
}
