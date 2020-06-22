<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\UserFollower;
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
 * Class FollowerRequestsController.
 */
class FollowerRequestsController extends AbstractController
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
     * @Route("/follower-requests", name="follower_requests")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $status = $request->query->get('status', UserFollower::STATUS_PENDING);
        if (!in_array($status, [
            UserFollower::STATUS_PENDING,
            UserFollower::STATUS_IGNORED,
        ])) {
            throw $this->createNotFoundException($this->translator->trans('status_not_found'));
        }

        $query = $this->em
            ->getRepository(UserFollower::class)
            ->findBy([
                'user' => $user,
                'status' => $status,
            ], [
                'createdAt' => 'DESC',
            ])
        ;
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('contents/users/follower_requests.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'status' => $status,
        ]);
    }

    /**
     * @Route("/follower-requests/{id}/delete", name="follower_requests.delete")
     *
     * @param mixed $id
     */
    public function delete($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        /** @var UserFollower|null $userFollower */
        $userFollower = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'id' => $id,
                'user' => $user,
            ])
        ;
        if (!$userFollower) {
            throw $this->createNotFoundException($this->translator->trans('follower_requests.user_follower_not_found', [], 'users'));
        }

        $this->em->remove($userFollower);
        $this->em->flush();

        $this->userActionManager->add(
            'follower_requests.delete',
            'A user follow was deleted',
            $userFollower->toArray()
        );

        $this->addFlash(
            'success',
            $this->translator->trans('follower_requests.delete.flash.success', [], 'users')
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('follower_requests');
    }

    /**
     * @Route("/follower-requests/{id}/approve", name="follower_requests.approve")
     *
     * @param mixed $id
     */
    public function approve($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        /** @var UserFollower|null $userFollower */
        $userFollower = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'id' => $id,
                'user' => $user,
            ])
        ;
        if (!$userFollower) {
            throw $this->createNotFoundException($this->translator->trans('follower_requests.user_follower_not_found', [], 'users'));
        }

        $userFollower->setStatus(UserFollower::STATUS_APPROVED);

        $this->em->persist($userFollower);
        $this->em->flush();

        $this->userActionManager->add(
            'follower_requests.approve',
            'A user follow was approved',
            $userFollower->toArray()
        );

        $this->addFlash(
            'success',
            $this->translator->trans('follower_requests.approve.flash.success', [], 'users')
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('follower_requests');
    }

    /**
     * @Route("/follower-requests/{id}/ignore", name="follower_requests.ignore")
     *
     * @param mixed $id
     */
    public function ignore($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        /** @var UserFollower|null $userFollower */
        $userFollower = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'id' => $id,
                'user' => $user,
            ])
        ;
        if (!$userFollower) {
            throw $this->createNotFoundException($this->translator->trans('follower_requests.user_follower_not_found', [], 'users'));
        }

        $userFollower->setStatus(UserFollower::STATUS_IGNORED);

        $this->em->persist($userFollower);
        $this->em->flush();

        $this->userActionManager->add(
            'follower_requests.ignore',
            'A user follow was ignored',
            $userFollower->toArray()
        );

        $this->addFlash(
            'success',
            $this->translator->trans('follower_requests.ignore.flash.success', [], 'users')
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('follower_requests');
    }
}
