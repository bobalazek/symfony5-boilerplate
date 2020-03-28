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
     * @Route("/oauth/{provider}", name="oauth")
     */
    public function index($provider)
    {
        return $this->redirect(
            $this->oauthManager->getOauthLoginUrl($provider)
        );
    }

    /**
     * @Route("/oauth/{provider}/callback", name="oauth.callback")
     */
    public function callback(
        $provider,
        Request $request,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    ) {
        $type = $request->getSession()->get('_oauth_type');
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

        $field = 'oauth' . ucfirst($provider) . 'Id';

        $user = $this->em
            ->getRepository(User::class)
            ->findOneBy([
                $field => $oauthUser['id'],
            ]);

        if ('link' === $type) {
            if (!$user) {
                $userMyself = $this->getUser();
                if ($userMyself) {
                    $method = 'set' . ucfirst($field);
                    $userMyself->{$method}(
                        $oauthUser['id']
                    );

                    $this->em->persist($userMyself);
                    $this->em->flush();
                }

                $this->addFlash(
                    'success',
                    $this->translator->trans('flash.linked_success', [
                        '{provider}' => $provider,
                    ], 'oauth')
                );
            } else {
                $this->addFlash(
                    'danger',
                    $this->translator->trans('flash.user_with_this_id_already_exists', [
                        '{provider}' => $provider,
                    ], 'oauth')
                );
            }
        } elseif ('login' === $type) {
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
                $this->translator->trans('flash.user_with_this_id_not_found', [
                    '{provider}' => $provider,
                ], 'login')
            );
        } elseif ('register' === $type) {
            if (!$user) {
                return $this->redirectToRoute('register', [
                    'oauth' => $provider,
                ]);
            }

            // Cleanup the oauth session
            $this->oauthManager->cleanup();

            $this->addFlash(
                'danger',
                $this->translator->trans('flash.user_with_this_id_already_exists', [
                    '{provider}' => $provider,
                ], 'login')
            );
        } else {
            $this->addFlash(
                'success',
                $this->translator->trans('flash.success', [
                    '{provider}' => $provider,
                ], 'oauth')
            );
        }

        return $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('home');
    }
}
