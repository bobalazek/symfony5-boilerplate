<?php

namespace App\Controller;

use App\Entity\UserExport;
use App\Manager\UserActionManager;
use App\Message\UserExportRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SettingsExportController.
 */
class SettingsExportController extends AbstractController
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
     * @Route("/settings/export", name="settings.export")
     */
    public function index(Request $request, MessageBusInterface $bus): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $requestAlert = null;
        $userExports = $this->em
            ->getRepository(UserExport::class)
            ->findBy([
                'user' => $user,
            ], [
                'createdAt' => 'DESC',
            ]);
        $lastUserExport = isset($userExports[0])
            ? $userExports[0]
            : null;
        if ($lastUserExport) {
            if (in_array(
                $lastUserExport->getStatus(),
                [
                    UserExport::STATUS_PENDING,
                    UserExport::STATUS_IN_PROGRESS,
                ]
            )) {
                $requestAlert = $this->translator->trans('export.alert.already_in_progress', [], 'settings');
            } elseif ((
                (new \DateTime())->getTimestamp() -
                $lastUserExport->getCreatedAt()->getTimestamp()
            ) < 3600) { // requested less then an hour ago
                $requestAlert = $this->translator->trans('export.alert.already_requested_recently', [], 'settings');
            }
        }

        $form = $this->createFormBuilder()->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($requestAlert) {
                throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
            }

            $userExport = new UserExport();

            $userExport
                ->setStatus(UserExport::STATUS_PENDING)
                ->setToken(md5(random_bytes(32)))
                ->setUser($user)
            ;

            $this->em->persist($userExport);
            $this->em->flush();

            $this->dispatchMessage(
                new UserExportRequest($userExport->getId())
            );

            $this->addFlash(
                'success',
                $this->translator->trans('export.flash.success', [], 'settings')
            );

            $this->userActionManager->add(
                'settings.export',
                'User export was requested'
            );

            return $this->redirectToRoute('settings.export');
        }

        return $this->render('contents/settings/export.html.twig', [
            'user_exports' => $userExports,
            'request_alert' => $requestAlert,
            'form' => $form->createView(),
        ]);
    }
}
