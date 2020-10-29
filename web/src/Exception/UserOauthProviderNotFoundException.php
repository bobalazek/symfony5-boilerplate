<?php

namespace App\Exception;

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
