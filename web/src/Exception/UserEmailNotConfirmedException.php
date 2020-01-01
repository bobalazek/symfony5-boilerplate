<?php

namespace App\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class UserEmailNotConfirmedException.
 */
class UserEmailNotConfirmedException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'User email was not confirmed yet.';
    }
}
