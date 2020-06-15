<?php

namespace App\Security\User;

use App\Entity\User;
use App\Exception\UserEmailNotConfirmedException;
use App\Exception\UserLockedException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserChecker.
 */
class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (null === $user->getEmailConfirmedAt()) {
            throw new UserEmailNotConfirmedException();
        }

        if ($user->isLocked()) {
            $message = 'User is locked';
            $messageData = [];

            if ($user->getLockedReason()) {
                $message = 'User is locked. Reason: %reason%';
                $messageData = [
                    '%reason%' => $user->getLockedReason(),
                ];
            }

            throw new UserLockedException($message, $messageData);
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }
    }
}
