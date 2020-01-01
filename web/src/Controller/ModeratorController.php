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
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em,
        \Swift_Mailer $mailer
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/moderator", name="moderator")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_MODERATOR');

        return $this->render('contents/moderator/index.html.twig');
    }
}
