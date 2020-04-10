<?php

namespace App\Controller;

use App\Form\LoginTfaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LoginTfaController.
 */
class LoginTfaController extends AbstractController
{
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

        $form = $this->createForm(LoginTfaType::class);
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

        // TODO

        return $this->render('contents/login/tfa/index.html.twig');
    }
}
