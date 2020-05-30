<?php

namespace App\Controller;

use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsDevicesController.
 */
class SettingsDevicesController extends AbstractController
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
     * @Route("/settings/devices", name="settings.devices")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userDevicesQueryBuilder = $this->em
            ->getRepository(UserDevice::class)
            ->createQueryBuilder('ud')
            ->where('ud.user = :user')
            ->orderBy('ud.lastActiveAt', 'DESC')
            ->setParameter('user', $this->getUser())
        ;

        $pagination = $paginator->paginate(
            $userDevicesQueryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('contents/settings/devices.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
