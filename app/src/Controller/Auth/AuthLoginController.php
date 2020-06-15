<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class AuthLoginController.
 */
class AuthLoginController extends AbstractController
{
    /**
     * @Route("/auth/login", name="auth.login")
     */
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('contents/auth/login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/auth/logout", name="auth.logout")
     */
    public function logout(): Response
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall

        return $this->redirectToRoute('auth.login');
    }
}
