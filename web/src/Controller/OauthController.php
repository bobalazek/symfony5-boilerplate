<?php

namespace App\Controller;

use App\Entity\User;
use App\Manager\OauthManager;
use App\Security\Guard\Authenticator\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class OauthController.
 */
class OauthController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var OauthManager
     */
    private $oauthManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        OauthManager $oauthManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->oauthManager = $oauthManager;
    }

    /**
     * @Route("/oauth/facebook", name="oauth.facebook")
     */
    public function facebook(Request $request)
    {
        $facebookClient = $this->oauthManager->getFacebookClient();
        $helper = $facebookClient->getRedirectLoginHelper();

        $type = $request->query->get('type', 'link');
        $request->getSession()->set('_oauth_type', $type);

        $referer = $request->headers->get('referer');
        $request->getSession()->set('_oauth_referer', $referer);

        $callbackUrl = $this->generateUrl(
            'oauth.facebook.callback',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $loginUrl = $helper->getLoginUrl(
            $callbackUrl,
            [
                'public_profile',
                'email',
            ]
        );

        return $this->redirect($loginUrl);
    }

    /**
     * @Route("/oauth/facebook/callback", name="oauth.facebook.callback")
     */
    public function facebookCallback(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $facebookClient = $this->oauthManager->getFacebookClient();
        $helper = $facebookClient->getRedirectLoginHelper();

        $type = $request->getSession()->get('_oauth_type');
        $referer = $request->getSession()->get('_oauth_referer');

        try {
            $accessToken = $helper->getAccessToken(
                $request->getUri() // hack, as the FB SDK detects the wrong uri
            );
        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            $this->addFlash(
                'danger',
                'Graph returned an error: ' . $e->getMessage()
            );

            if ($referer) {
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('home');
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            $this->addFlash(
                'danger',
                'Facebook SDK returned an error: ' . $e->getMessage()
            );

            if ($referer) {
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('home');
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
                $this->addFlash(
                    'danger',
                    'Something went wrong. Error: ' .
                    $helper->getError()
                );

                if ($referer) {
                    return $this->redirect($referer);
                }

                return $this->redirectToRoute('home');
            } else {
                $this->addFlash(
                    'danger',
                    'Something went wrong'
                );

                if ($referer) {
                    return $this->redirect($referer);
                }

                return $this->redirectToRoute('home');
            }
        }

        $accessTokenString = (string) $accessToken;
        $request->getSession()->set('_facebook_access_token', $accessTokenString);

        $facebookUser = $this->oauthManager->getFacebookUser($accessTokenString);
        $facebookId = $facebookUser->getId();

        if ('link' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthFacebookId(
                    $facebookId
                );

            if (!$user) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $userMyself->setOauthFacebookId($facebookId);

                    $this->em->persist($userMyself);
                    $this->em->flush();
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.facebook_linked_success', [], 'oauth')
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.user_with_this_facebook_id_already_exists', [], 'oauth')
                );
            }
        } elseif ('login' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthFacebookId(
                    $facebookId
                );

            if ($user) {
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $formAuthenticator,
                    'main'
                );
            }

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_facebook_id_not_found', [], 'login')
            );
        } elseif ('register' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthFacebookId(
                    $facebookId
                );

            if (!$user) {
                return $this->redirectToRoute('register', [
                    'oauth' => 'facebook',
                ]);
            }

            // Remove, so we can't reused it anymore
            $request->getSession()->set('_facebook_access_token', null);

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_facebook_id_already_exists', [], 'login')
            );
        } else {
            $this->addFlash(
                'success',
                $this->translator->trans('facebook.flash.success', [], 'oauth')
            );
        }

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/oauth/google", name="oauth.google")
     */
    public function google(Request $request)
    {
        $type = $request->query->get('type', 'link');
        $request->getSession()->set('_oauth_type', $type);

        $referer = $request->headers->get('referer');
        $request->getSession()->set('_oauth_referer', $referer);

        $googleClient = $this->oauthManager->getGoogleClient();

        $authUrl = $googleClient->createAuthUrl();

        return $this->redirect($authUrl);
    }

    /**
     * @Route("/oauth/google/callback", name="oauth.google.callback")
     */
    public function googleCallback(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $googleClient = $this->oauthManager->getGoogleClient();

        $code = $request->query->get('code');

        if (!$code) {
            return $this->redirectToRoute('oauth.google');
        }

        $type = $request->getSession()->get('_oauth_type');
        $referer = $request->getSession()->get('_oauth_referer');

        $googleClient->authenticate($code);

        $accessToken = $googleClient->getAccessToken();
        $request->getSession()->set('_google_access_token', $accessToken);

        $googleUser = $this->oauthManager->getGoogleUser($accessToken);
        $googleId = $googleUser->getUserId();

        if ('link' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthGoogleId(
                    $googleId
                );

            if (!$user) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $userMyself->setOauthGoogleId($googleId);

                    $this->em->persist($userMyself);
                    $this->em->flush();
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.google_linked_success', [], 'oauth')
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.user_with_this_google_id_already_exists', [], 'oauth')
                );
            }
        } elseif ('login' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthGoogleId(
                    $googleId
                );

            if ($user) {
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $formAuthenticator,
                    'main'
                );
            }

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_google_id_not_found', [], 'login')
            );
        } elseif ('register' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthGoogleId(
                    $googleId
                );

            if (!$user) {
                return $this->redirectToRoute('register', [
                    'oauth' => 'google',
                ]);
            }

            // Remove, so we can't reused it anymore
            $request->getSession()->set('_google_access_token', null);

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_google_id_already_exists', [], 'login')
            );
        } else {
            $this->addFlash(
                'success',
                $this->translator->trans('google.flash.success', [], 'oauth')
            );
        }

        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('home');
    }
}
