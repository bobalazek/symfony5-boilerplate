<?php

namespace App\Manager;

use Facebook\Facebook;
use Google_Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class OauthManager.
 */
class OauthManager
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Facebook
     */
    private $facebookClient;

    /**
     * @var Google_Client
     */
    private $googleClient;

    public function __construct(ParameterBagInterface $params, RouterInterface $router)
    {
        $this->params = $params;
        $this->router = $router;
    }

    public function getFacebookClient(): Facebook
    {
        if (!$this->facebookClient) {
            $facebookCredentials = $this->params->get('app.oauth.facebook');
            $this->facebookClient = new Facebook([
                'app_id' => $facebookCredentials['id'],
                'app_secret' => $facebookCredentials['secret'],
                'default_graph_version' => $facebookCredentials['version'],
            ]);
        }

        return $this->facebookClient;
    }

    public function getFacebookUser($accessToken)
    {
        $response = $this->getFacebookClient()->get(
            '/me?fields=id',
            $accessToken
        );

        return $response->getGraphUser();
    }

    public function getGoogleClient($redirectUri = null): Google_Client
    {
        if (!$redirectUri) {
            $redirectUri = $this->router->generate(
                'oauth.google.callback',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        if (!$this->googleClient) {
            $googleCredentials = $this->params->get('app.oauth.google');
            $this->googleClient = new Google_Client();
            $this->googleClient->setClientId($googleCredentials['id']);
            $this->googleClient->setClientSecret($googleCredentials['secret']);
            $this->googleClient->setRedirectUri($redirectUri);
            $this->googleClient->addScope('https://www.googleapis.com/auth/userinfo.email');
            $this->googleClient->addScope('https://www.googleapis.com/auth/userinfo.profile');
            $this->googleClient->setIncludeGrantedScopes(true);
        }

        return $this->googleClient;
    }

    public function getGoogleUser($accessToken)
    {
        $client = $this->getGoogleClient();

        $client->setAccessToken($accessToken);

        $oauth = new \Google_Service_Oauth2($client);

        return $oauth->tokeninfo();
    }
}
