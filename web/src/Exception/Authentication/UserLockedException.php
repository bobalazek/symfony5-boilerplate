<?php

namespace App\Exception\Authentication;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class UserLockedException.
 */
class UserLockedException extends CustomUserMessageAuthenticationException
{
}
