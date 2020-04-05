<?php

namespace App\Controller;

use App\Entity\UserTfaMethod;
use App\Form\SettingsUserTfaMethodType;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsTfaController.
 */
class SettingsTfaController extends AbstractController
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
     * @Route("/settings/tfa", name="settings.tfa")
     */
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        // TODO: save it in params
        $methods = [
            'email' => $this->translator->trans('Email'),
            'authenticator' => $this->translator->trans('Authenticator'),
            'recovery_codes' => $this->translator->trans('Recovery codes'),
        ];
        $methodsEnabled = [];

        foreach ($user->getUserTfaMethods() as $userTfaMethod) {
            if (!$userTfaMethod->isEnabled()) {
                continue;
            }

            $methodsEnabled[] = $userTfaMethod->getMethod();
        }

        return $this->render('contents/settings/tfa/index.html.twig', [
            'methods' => $methods,
            'methods_enabled' => $methodsEnabled,
        ]);
    }

    /**
     * @Route("/login/tfa/{method}", name="settings.tfa.edit")
     */
    public function edit($method, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // TODO: save it in params
        if (!in_array($method, [
            'email',
            'authenticator',
            'recovery_codes',
        ])) {
            $this->addFlash(
                'danger',
                $this->translator->trans('tfa.flash.method_does_not_exist', [], 'settings')
            );

            return $this->redirectToRoute('settings.tfa');
        }

        $user = $this->getUser();

        $userTfaMethod = $this->em
            ->getRepository(UserTfaMethod::class)
            ->findOneBy([
                'user' => $user,
                'method' => $method,
            ])
        ;
        if (!$userTfaMethod) {
            $userTfaMethod = new UserTfaMethod();
            $userTfaMethod
                ->setMethod($method)
                ->setUser($user)
            ;

            $this->em->persist($userTfaMethod);
            $this->em->flush();
        }

        $form = $this->createForm(SettingsUserTfaMethodType::class, $userTfaMethod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($userTfaMethod);
            $this->em->flush();

            $this->addFlash(
                'success',
                $this->translator->trans('tfa.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.tfa.edit',
                'User TFA method was changed.',
                [
                    'method' => $method,
                ]
            );

            return $this->redirectToRoute('settings.tfa');
        }

        return $this->render('contents/settings/tfa/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
