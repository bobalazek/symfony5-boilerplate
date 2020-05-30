<?php

namespace App\Controller\Settings;

use App\Entity\UserAction;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsActionsController.
 */
class SettingsActionsController extends AbstractController
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

    public function __construct(
        TranslatorInterface $translator,
        ParameterBagInterface $params,
        EntityManagerInterface $em
    ) {
        $this->translator = $translator;
        $this->params = $params;
        $this->em = $em;
    }

    /**
     * @Route("/settings/actions", name="settings.actions")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userActionsQueryBuilder = $this->em
            ->getRepository(UserAction::class)
            ->createQueryBuilder('ua')
            ->where('ua.user = :user')
            ->orderBy('ua.createdAt', 'DESC')
            ->setParameter('user', $this->getUser())
        ;

        $pagination = $paginator->paginate(
            $userActionsQueryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('contents/settings/actions.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
