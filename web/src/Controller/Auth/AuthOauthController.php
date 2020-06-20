<?php

namespace App\Controller\Auth;

use App\Entity\UserOauthProvider;
use App\Manager\OauthManager;
use App\Security\Guard\Authenticator\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthOauthController.
 */
class AuthOauthController extends AbstractController
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
     * @Route("/auth/oauth/{provider}", name="auth.oauth")
     *
     * @param mixed $provider
     */
    public function index($provider)
    {
        return $this->redirect(
            $this->oauthManager->getOauthLoginUrl($provider)
        );
    }

    /**
     * @Route("/auth/oauth/{provider}/callback", name="auth.oauth.callback")
     *
     * @param mixed $provider
     */
    public function callback(
        $provider,
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $action = $request->getSession()->get('_oauth_action');
        $referer = $request->getSession()->get('_oauth_referer');

        try {
            $oauthUser = $this->oauthManager->getUser($provider);
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

        $userOauthProvider = $this->em
            ->getRepository(UserOauthProvider::class)
            ->findOneBy([
                'provider' => $provider,
                'providerId' => $oauthUser->getId(),
            ])
        ;

        if ('link' === $action) {
            if (!$userOauthProvider) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $userOauthProvider = new UserOauthProvider();
                    $userOauthProvider->setProvider($provider);
                    $userOauthProvider->setProviderId($oauthUser->getId());

                    $userMyself->addUserOauthProvider($userOauthProvider);

                    $this->em->persist($userMyself);
                    $this->em->flush();
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('oauth.flash.linked_success', [
                        'provider' => $provider,
                    ], 'auth')
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('oauth.flash.user_with_this_id_already_exists', [
                        'provider' => $provider,
                    ], 'auth')
                );
            }
        } elseif ('login' === $action) {
            if ($userOauthProvider) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('login.oauth.flash.success', [
                        'provider' => $provider,
                    ], 'auth')
                );

                return $guardHandler->authenticateUserAndHandleSuccess(
                    $userOauthProvider->getUser(),
                    $request,
                    $formAuthenticator,
                    'main'
                );
            }

            $this->addFlash(
                'danger',
                $this->translator->trans('login.flash.user_with_this_id_not_found', [
                    'provider' => $provider,
                ], 'auth')
            );
        } elseif ('register' === $action) {
            if (!$userOauthProvider) {
                return $this->redirectToRoute('auth.register', [
                    'oauth' => $provider,
                ]);
            }

            // Cleanup the oauth session
            $this->oauthManager->cleanup($request);

            $this->addFlash(
                'danger',
                $this->translator->trans('login.flash.user_with_this_id_already_exists', [
                    'provider' => $provider,
                ], 'auth')
            );
        }

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('home');
    }
}
