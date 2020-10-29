<?php

namespace App\Manager;

use App\Entity\UserOauthProvider;
use App\Exception\UserOauthProviderNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var \Facebook\Facebook|null
     */
    private $facebookClient;

    /**
     * @var \Google_Client|null
     */
    private $googleClient;

    public function __construct(
        ParameterBagInterface $params,
        RouterInterface $router,
        RequestStack $requestStack
    ) {
        $this->params = $params;
        $this->router = $router;
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $provider
     *
     * @throws \Exception
     *
     * @return Oauth\OauthUser
     */
    public function getUser($provider)
    {
        if (UserOauthProvider::PROVIDER_FACEBOOK === $provider) {
            return $this->getFacebookUser();
        }

        if (UserOauthProvider::PROVIDER_GOOGLE === $provider) {
            return $this->getGoogleUser();
        }

        throw new UserOauthProviderNotFoundException($provider);
    }

    /**
     * @param string $provider
     *
     * @throws \Exception if provider does not exist
     *
     * @return string
     */
    public function getOauthLoginUrl($provider)
    {
        $request = $this->requestStack->getCurrentRequest();

        $action = $request->query->get('action', 'link');
        $request->getSession()->set('_oauth_action', $action);

        $referer = $request->headers->get('referer');
        $request->getSession()->set('_oauth_referer', $referer);

        if (UserOauthProvider::PROVIDER_FACEBOOK === $provider) {
            $callbackUrl = $this->router->generate(
                'auth.oauth.callback',
                [
                    'provider' => UserOauthProvider::PROVIDER_FACEBOOK,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $facebookClient = $this->getFacebookClient();
            $helper = $facebookClient->getRedirectLoginHelper();

            $facebookCredentials = $this->params->get('app.oauth.facebook');
            $scope = explode(',', $facebookCredentials['scope']);

            return $helper->getLoginUrl(
                $callbackUrl,
                $scope
            );
        }

        if (UserOauthProvider::PROVIDER_GOOGLE === $provider) {
            $googleClient = $this->getGoogleClient();

            return $googleClient->createAuthUrl();
        }

        throw new UserOauthProviderNotFoundException($provider);
    }

    public function cleanup(Request $request)
    {
        $request->getSession()->set('_facebook_access_token', null);
        $request->getSession()->set('_google_access_token', null);
    }

    /* Facebook */
    public function getFacebookClient(): \Facebook\Facebook
    {
        if (!$this->facebookClient) {
            $facebookCredentials = $this->params->get('app.oauth.facebook');
            $this->facebookClient = new \Facebook\Facebook([
                'app_id' => $facebookCredentials['id'],
                'app_secret' => $facebookCredentials['secret'],
                'default_graph_version' => $facebookCredentials['version'],
            ]);
        }

        return $this->facebookClient;
    }

    public function getFacebookUser(): Oauth\OauthUser
    {
        $request = $this->requestStack->getCurrentRequest();
        $accessToken = $request->getSession()->get('_facebook_access_token');
        $facebookClient = $this->getFacebookClient();
        $helper = $facebookClient->getRedirectLoginHelper();

        if (!$accessToken) {
            $accessToken = $helper->getAccessToken(
                $request->getUri() // hack, as the FB SDK detects the wrong uri
            );
        }

        $accessTokenString = (string) $accessToken;
        $request->getSession()->set('_facebook_access_token', $accessTokenString);

        $facebookUserResponse = $facebookClient->get(
            '/me?fields=id,email,name',
            $accessToken
        );

        $facebookUser = $facebookUserResponse->getGraphUser();

        $oauthUser = new Oauth\OauthUser();
        $oauthUser
            ->setId($facebookUser->getId())
            ->setEmail($facebookUser->getEmail())
            ->setName($facebookUser->getName())
            ->setRawData($facebookUser->asArray())
        ;

        return $oauthUser;
    }

    /* Google */
    public function getGoogleClient(): \Google_Client
    {
        if (!$this->googleClient) {
            $callbackUrl = $this->router->generate(
                'auth.oauth.callback',
                [
                    'provider' => 'google',
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $googleCredentials = $this->params->get('app.oauth.google');

            $this->googleClient = new \Google_Client();
            $this->googleClient->setClientId($googleCredentials['id']);
            $this->googleClient->setClientSecret($googleCredentials['secret']);
            $this->googleClient->addScope('openid');
            $this->googleClient->addScope('https://www.googleapis.com/auth/userinfo.email');
            $this->googleClient->addScope('https://www.googleapis.com/auth/userinfo.profile');
            $this->googleClient->setIncludeGrantedScopes(true);
            $this->googleClient->setRedirectUri($callbackUrl);
        }

        return $this->googleClient;
    }

    public function getGoogleUser(): Oauth\OauthUser
    {
        $request = $this->requestStack->getCurrentRequest();
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
        $googleUserinfo = $oauth->userinfo->get();

        $oauthUser = new Oauth\OauthUser();
        $oauthUser
            ->setId($googleUser->getUserId())
            ->setEmail($googleUser->getEmail())
            ->setName($googleUserinfo->getName())
            ->setRawData([
                'tokeninfo' => json_decode(json_encode($googleUser), true),
                'userinfo' => json_decode(json_encode($googleUserinfo), true),
            ])
        ;

        return $oauthUser;
    }
}
