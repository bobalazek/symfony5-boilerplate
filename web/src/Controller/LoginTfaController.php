<?php

namespace App\Controller;

use App\Entity\UserTfaMethod;
use App\Form\LoginTfaType;
use App\Manager\GoogleAuthenticatorManager;
use App\Manager\UserActionManager;
use App\Manager\UserTfaManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class LoginTfaController.
 */
class LoginTfaController extends AbstractController
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
     * @var GoogleAuthenticatorManager
     */
    private $googleAuthenticatorManager;

    /**
     * @var UserTfaManager
     */
    private $userTfaManager;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        UserActionManager $userActionManager,
        GoogleAuthenticatorManager $googleAuthenticatorManager,
        UserTfaManager $userTfaManager
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->userActionManager = $userActionManager;
        $this->googleAuthenticatorManager = $googleAuthenticatorManager;
        $this->userTfaManager = $userTfaManager;
    }

    /**
     * @Route("/login/tfa", name="login.tfa")
     */
    public function index(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        $method = $request->getSession()->get('tfa_method');
        $inProgress = $request->getSession()->get('tfa_in_progress');
        if (!$inProgress) {
            return $this->redirectToRoute('home');
        }

        $isEmailMethod = UserTfaMethod::METHOD_EMAIL === $method;

        $form = $this->createForm(LoginTfaType::class, [], [
            'show_code_field' => !$isEmailMethod,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            $request->getSession()->remove('tfa_method');
            $request->getSession()->remove('tfa_in_progress');

            $this->userActionManager->add(
                'login.tfa',
                'User successfully entered the 2FA code',
                [
                    'method' => $method,
                ]
            );

            return $this->redirectToRoute('settings.tfa');
        }

        $methods = $this->params->get('app.tfa_methods');
        $availableMethods = $this->userTfaManager->getAvailableMethods();

        $switchMethod = $request->query->get('switch_method');
        if (
            $switchMethod &&
            in_array($switchMethod, $availableMethods)
        ) {
            $request->getSession()->set('tfa_method', $switchMethod);

            return $this->redirectToRoute('settings.tfa');
        }

        return $this->render('contents/login/tfa.html.twig', [
            'form' => $form->createView(),
            'method' => $method,
            'methods' => $methods,
            'available_methods' => $availableMethods,
        ]);
    }
}
