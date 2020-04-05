<?php

namespace App\Manager;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class GoogleAuthenticatorManager.
 */
class GoogleAuthenticatorManager
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var GoogleAuthenticator
     */
    private $googleAuthenticator;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    /**
     * @return string
     */
    public function getCode(string $secret)
    {
        return $this->googleAuthenticator->getCode($secret);
    }

    /**
     * @return bool
     */
    public function checkCode(string $secret, string $code)
    {
        return $this->googleAuthenticator->checkCode($secret, $code);
    }

    /**
     * @return string
     */
    public function generateSecret()
    {
        return $this->googleAuthenticator->generateSecret();
    }

    /**
     * @param string $accountName basically the id of a user in email format: [user_id]@[hostname]
     * @param string $issuer
     *
     * @return string
     */
    public function generateQr(string $accountName, string $secret, string $issuer = null, int $size)
    {
        return GoogleQrUrl::generate($accountName, $secret, $issuer, $size);
    }
}
