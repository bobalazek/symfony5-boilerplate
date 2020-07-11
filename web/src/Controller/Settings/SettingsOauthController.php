<?php

namespace App\Controller\Settings;

use App\Entity\User;
use App\Entity\UserOauthProvider;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsOauthController.
 */
class SettingsOauthController extends AbstractController
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
     * @var UserActionManager
     */
    private $userActionManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
    }

    /**
     * @Route("/settings/oauth", name="settings.oauth")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $providers = $this->params->get('app.oauth_providers');

        foreach ($providers as $providerKey => $provider) {
            $providers[$providerKey]['_is_linked'] = null !== $this->em
                ->getRepository(UserOauthProvider::class)
                ->findOneBy([
                    'user' => $user,
                    'provider' => $providerKey,
                ])
            ;
        }

        $action = $request->query->get('action');
        if ('unlink' === $action) {
            $provider = $request->query->get('provider');

            $userOauthProvider = $this->em
                ->getRepository(UserOauthProvider::class)
                ->findOneBy([
                    'user' => $user,
                    'provider' => $provider,
                ])
            ;
            if (!$userOauthProvider) {
                throw $this->createNotFoundException($this->translator->trans('oauth.unlink.provider_not_found', [], 'settings'));
            }

            $this->em->remove($userOauthProvider);
            $this->em->flush();

            $this->userActionManager->add(
                'settings.oauth.' . $provider . '.unlink',
                'User has successfully unlinked their ' . $provider . ' account',
                [
                    'provider' => $provider,
                ]
            );

            $this->addFlash(
                'success',
                $this->translator->trans('oauth.flash.unlinked_success', [
                    '{provider}' => $provider,
                ], 'settings')
            );

            return $this->redirectToRoute('settings.oauth');
        }

        return $this->render('contents/settings/oauth.html.twig', [
            'providers' => $providers,
        ]);
    }
}
