<?php

namespace App\Controller;

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

        $user = $this->getUser();

        $action = $request->query->get('action');
        if ('facebook_unlink' === $action) {
            $user->setOauthFacebookId(null);

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('oauth.flash.facebook_unlinked_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.oauth.facebook.unlink',
                'User has successfully unlinked their facebook account'
            );

            return $this->redirectToRoute('settings.oauth');
        } elseif ('google_unlink' === $action) {
            $user->setOauthGoogleId(null);

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('oauth.flash.google_unlinked_success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.oauth.google.unlink',
                'User has successfully unlinked their google account'
            );

            return $this->redirectToRoute('settings.oauth');
        }

        return $this->render('contents/settings/oauth.html.twig');
    }
}
