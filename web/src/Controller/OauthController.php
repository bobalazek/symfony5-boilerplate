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
 *
 * TODO: implement https://github.com/knpuniversity/oauth2-client-bundle
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
        return $this->redirect(
            $this->oauthManager->getOauthLoginUrl('facebook')
        );
    }

    /**
     * @Route("/oauth/facebook/callback", name="oauth.facebook.callback")
     */
    public function facebookCallback(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $type = $request->getSession()->get('_oauth_type');
        $referer = $request->getSession()->get('_oauth_referer');

        try {
            $oauthUser = $this->oauthManager->getFacebookUser($request);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                'Something went wrong. Error: ' .
                $e->getMessage()
            );

            return $referer
                ? $this->redirect($referer)
                : $this->redirectToRoute('home');
        }

        if ('link' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthFacebookId(
                    $oauthUser['id']
                );

            if (!$user) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $userMyself->setOauthFacebookId(
                        $oauthUser['id']
                    );

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
                    $oauthUser['id']
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
                    $oauthUser['id']
                );

            if (!$user) {
                return $this->redirectToRoute('register', [
                    'oauth' => 'facebook',
                ]);
            }

            // Cleanup the oauth session
            $this->oauthManager->cleanup();

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

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('home');
    }

    /**
     * @Route("/oauth/google", name="oauth.google")
     */
    public function google(Request $request)
    {
        return $this->redirect(
            $this->oauthManager->getOauthLoginUrl('google')
        );
    }

    /**
     * @Route("/oauth/google/callback", name="oauth.google.callback")
     */
    public function googleCallback(
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $type = $request->getSession()->get('_oauth_type');
        $referer = $request->getSession()->get('_oauth_referer');

        try {
            $oauthUser = $this->oauthManager->getGoogleUser($request);
        } catch (\Exception $e) {
            $this->addFlash(
                'danger',
                'Something went wrong. Error: ' .
                $e->getMessage()
            );

            return $referer
                ? $this->redirect($referer)
                : $this->redirectToRoute('home');
        }

        if ('link' === $type) {
            $user = $this->em
                ->getRepository(User::class)
                ->findOneByOauthGoogleId(
                    $oauthUser['id']
                );

            if (!$user) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $userMyself->setOauthGoogleId(
                        $oauthUser['id']
                    );

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
                    $oauthUser['id']
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
                    $oauthUser['id']
                );

            if (!$user) {
                return $this->redirectToRoute('register', [
                    'oauth' => 'google',
                ]);
            }

            // Cleanup the oauth session
            $this->oauthManager->cleanup();

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

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('home');
    }
}
