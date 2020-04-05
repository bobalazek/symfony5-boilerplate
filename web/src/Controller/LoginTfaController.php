<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class LoginTfaController.
 */
class LoginTfaController extends AbstractController
{
    /**
     * @Route("/login/tfa", name="login.tfa")
     */
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }

        // TODO

        return $this->render('contents/login/tfa/index.html.twig');
    }
}
