<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ModeratorController.
 */
class ModeratorController extends AbstractController
{
    /**
     * @Route("/moderator", name="moderator")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        return $this->render('contents/moderator/index.html.twig');
    }
}
