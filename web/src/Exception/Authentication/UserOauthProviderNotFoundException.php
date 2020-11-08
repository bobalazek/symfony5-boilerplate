<?php

namespace App\Exception\Authentication;

/**
 * Class UserOauthProviderNotFoundException.
 */
class UserOauthProviderNotFoundException extends \Exception
{
    public function __construct($provider)
    {
        parent::__construct('Provider "' . $provider . '" does not exist.');
    }
}
