<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
