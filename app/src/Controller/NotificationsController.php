<?php

namespace App\Controller;

use App\Entity\UserNotification;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class NotificationsController.
 */
class NotificationsController extends AbstractController
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
     * @Route("/notifications", name="notifications")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $query = $this->em
            ->getRepository(UserNotification::class)
            ->createQueryBuilder('un')
            ->where('un.user = :user')
            ->orderBy('un.createdAt', 'DESC')
            ->setParameter('user', $user)
            ->getQuery()
        ;
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            50
        );

        foreach ($pagination as $userNotification) {
            $userNotification->setSeenAt(new \DateTime());
            $this->em->persist($userNotification);
        }

        $this->em->flush();

        return $this->render('contents/notifications/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/notifications/{id}/read", name="notifications.read")
     *
     * @param mixed $id
     */
    public function read($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userNotification = $this->_get($id);

        $userNotification->setReadAt(new \DateTime());

        $this->em->persist($userNotification);
        $this->em->flush();

        $this->userActionManager->add(
            'notifications.read',
            'The user has read the notification',
            [
                'id' => $userNotification->getId(),
            ]
        );

        $this->addFlash(
            'success',
            $this->translator->trans('read.flash.success', [], 'notifications')
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }
    }

    /**
     * @Route("/notifications/{id}/unread", name="notifications.unread")
     *
     * @param mixed $id
     */
    public function unread($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userNotification = $this->_get($id);

        $userNotification->setReadAt(null);

        $this->em->persist($userNotification);
        $this->em->flush();

        $this->userActionManager->add(
            'notifications.unread',
            'The user has unread the notification',
            [
                'id' => $userNotification->getId(),
            ]
        );

        $this->addFlash(
            'success',
            $this->translator->trans('unread.flash.success', [], 'notifications')
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('notifications');
    }

    /* Helpers */
    private function _get($id)
    {
        /** @var UserNotification $userNotification */
        $userNotification = $this->em
            ->getRepository(UserNotification::class)
            ->findOneBy([
                'id' => $id,
                'user' => $this->getUser(),
            ])
        ;
        if (!$userNotification) {
            throw $this->createNotFoundException($this->translator->trans('user_notification_not_found', [], 'notifications'));
        }

        return $userNotification;
    }
}
