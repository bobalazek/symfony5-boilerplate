<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

/**
 * Class UserLockedException.
 */
class UserLockedException extends CustomUserMessageAuthenticationException
{
}
