<?php

namespace App\Controller\Settings;

use App\Entity\UserDevice;
use App\Manager\UserActionManager;
use App\Manager\UserDeviceManager;
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

    /**
     * @var UserActionManager
     */
    private $userActionManager;

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
     * @Route("/settings/devices", name="settings.devices")
     */
    public function index(
        Request $request,
        PaginatorInterface $paginator,
        UserDeviceManager $userDeviceManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $userDevicesQueryBuilder = $this->em
            ->getRepository(UserDevice::class)
            ->createQueryBuilder('ud')
            ->where('ud.user = :user AND ud.invalidated = false')
            ->orderBy('ud.lastActiveAt', 'DESC')
            ->setParameter('user', $user)
        ;

        $pagination = $paginator->paginate(
            $userDevicesQueryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('contents/settings/devices.html.twig', [
            'pagination' => $pagination,
            'user_device_current' => $userDeviceManager->get($user, $request),
        ]);
    }

    /**
     * @Route("/settings/devices/{id}/invalidate", name="settings.devices.invalidate")
     *
     * @param mixed $id
     */
    public function invalidate($id, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var UserDevice $userDevice */
        $userDevice = $this->em
            ->getRepository(UserDevice::class)
            ->findOneBy([
                'id' => $id,
                'user' => $this->getUser(),
                'invalidated' => false,
            ])
        ;
        if (!$userDevice) {
            $this->addFlash(
                'danger',
                $this->translator->trans('devices.flash.invalidate.device_not_found', [], 'settings')
            );

            return $this->redirectToRoute('settings.devices');
        }

        $userDevice->setInvalidated(true);

        $this->em->persist($userDevice);
        $this->em->flush();

        $this->userActionManager->add(
            'settings.devices.invalidate',
            'User has successfully invalidated their device',
            [
                'id' => $userDevice->getId(),
                'uuid' => $userDevice->getUuid(),
                'name' => $userDevice->getName(),
            ]
        );

        $this->addFlash(
            'success',
            $this->translator->trans('devices.flash.invalidate.success', [], 'settings')
        );

        return $this->redirectToRoute('settings.devices');
    }
}
