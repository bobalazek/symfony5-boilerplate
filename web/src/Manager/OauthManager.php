<?php

namespace App\Manager;

use Facebook\Facebook;
use Google_Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
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

    public function getFacebookUser(Request $request)
    {
        $accessToken = $request->getSession()->get('_facebook_access_token');
        $facebookClient = $this->getFacebookClient();
        $helper = $facebookClient->getRedirectLoginHelper();

        if (!$accessToken) {
            $accessToken = $helper->getAccessToken(
                $request->getUri() // hack, as the FB SDK detects the wrong uri
            );
        }

        $accessTokenString = (string)$accessToken;
        $request->getSession()->set('_facebook_access_token', $accessTokenString);

        $facebookUserResponse = $this->getFacebookClient()->get(
            '/me?fields=id,name,first_name,middle_name,last_name,email',
            $accessToken
        );
        $facebookUser = $facebookUserResponse->getGraphUser();

        return [
            'id' => $facebookUser->getId(),
            'name' => $facebookUser->getName(),
            'first_name' => $facebookUser->getFirstName(),
            'middle_name' => $facebookUser->getMiddleName(),
            'last_name' => $facebookUser->getLastName(),
            'email' => $facebookUser->getEmail(),
        ];
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

    public function getGoogleUser(Request $request)
    {
        $accessToken = $request->getSession()->get('_google_access_token');

        $client = $this->getGoogleClient();

        if (!$accessToken) {
            $code = $request->query->get('code');
            if (!$code) {
                throw new \Exception('The "code" query parameter is not provided.');
            }

            $client->authenticate($code);

            $accessToken = $client->getAccessToken();
        }

        $request->getSession()->set('_google_access_token', $accessToken);

        $client->setAccessToken($accessToken);

        $oauth = new \Google_Service_Oauth2($client);

        $googleUser = $oauth->tokeninfo();

        return [
            'id' => $googleUser->getUserId(),
            'email' => $googleUser->getEmail(),
            'verified_email' => $googleUser->getVerifiedEmail(),
        ];
    }
}
