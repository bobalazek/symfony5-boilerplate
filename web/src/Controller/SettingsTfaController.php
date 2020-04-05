<?php

namespace App\Controller;

use App\Entity\UserTfaMethod;
use App\Form\SettingsUserTfaMethodType;
use App\Manager\GoogleAuthenticatorManager;
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

    /**
     * @var array
     */
    private $methods;

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

        $methods = $this->params->get('app.tfa_methods');
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
     *
     * @param mixed $method
     */
    public function edit(
        $method,
        Request $request,
        GoogleAuthenticatorManager $googleAuthenticatorManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $methods = $this->params->get('app.tfa_methods');
        if (!in_array($method, array_keys($methods))) {
            $this->addFlash(
                'danger',
                $this->translator->trans('tfa.flash.method_does_not_exist', [], 'settings')
            );

            return $this->redirectToRoute('settings.tfa');
        }

        $user = $this->getUser();

        $methodData = $methods[$method];
        $userTfaMethod = $this->em
            ->getRepository(UserTfaMethod::class)
            ->findOneBy([
                'user' => $user,
                'method' => $method,
            ])
        ;
        if (!$userTfaMethod) {
            $data = null;

            if (UserTfaMethod::METHOD_AUTHENTICATOR === $method) {
                // TODO
                $data = [
                    'secret' => $googleAuthenticatorManager->generateSecret(),
                ];
            } elseif (UserTfaMethod::METHOD_RECOVERY_CODES === $method) {
                // TODO
            }

            $userTfaMethod = new UserTfaMethod();
            $userTfaMethod
                ->setMethod($method)
                ->setData($data)
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
            'method' => $method,
            'heading' => $methodData['label'],
        ]);
    }
}
