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
     * @Route("/oauth/{provider}", name="oauth")
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
     * @Route("/oauth/{provider}/callback", name="oauth.callback")
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
                    $this->translator->trans('flash.linked_success', [
                        'provider' => $provider,
                    ], 'oauth')
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.user_with_this_id_already_exists', [
                        'provider' => $provider,
                    ], 'oauth')
                );
            }
        } elseif ('login' === $action) {
            if ($userOauthProvider) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.success', [
                        'provider' => $provider,
                    ], 'oauth')
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
                $this->translator->trans('flash.user_with_this_id_not_found', [
                    'provider' => $provider,
                ], 'login')
            );
        } elseif ('register' === $action) {
            if (!$userOauthProvider) {
                return $this->redirectToRoute('register', [
                    'oauth' => $provider,
                ]);
            }

            // Cleanup the oauth session
            $this->oauthManager->cleanup($request);

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_id_already_exists', [
                    'provider' => $provider,
                ], 'login')
            );
        }

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('home');
    }
}
