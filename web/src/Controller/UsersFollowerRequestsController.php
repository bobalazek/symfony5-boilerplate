<?php

namespace App\Controller;

use App\Entity\UserFollower;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersFollowerRequestsController.
 */
class UsersFollowerRequestsController extends AbstractUsersController
{
    /**
     * @Route("/users/me/follower-requests", name="users.follower_requests")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $status = $request->query->get('status', UserFollower::STATUS_PENDING);
        if (!in_array($status, [
            UserFollower::STATUS_PENDING,
            UserFollower::STATUS_IGNORED,
        ])) {
            throw $this->createNotFoundException($this->translator->trans('status_not_found'));
        }

        $query = $this->em->getRepository(UserFollower::class)
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
     * @Route("/follower-requests/{id}/delete", name="users.follower_requests.delete")
     *
     * @param mixed $id
     */
    public function delete($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $userFollower = $this->em->getRepository(UserFollower::class)
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

        $this->addFlash(
            'success',
            $this->translator->trans('follower_requests.delete.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.follower_requests.delete',
            'A user follow was deleted',
            $userFollower->toArray()
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.follower_requests');
    }

    /**
     * @Route("/follower-requests/{id}/approve", name="users.follower_requests.approve")
     *
     * @param mixed $id
     */
    public function approve($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $userFollower = $this->em->getRepository(UserFollower::class)
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

        $this->addFlash(
            'success',
            $this->translator->trans('approve.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.follower_requests.approve',
            'A user follow was approved',
            $userFollower->toArray()
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.follower_requests');
    }

    /**
     * @Route("/follower-requests/{id}/ignore", name="users.follower_requests.ignore")
     *
     * @param mixed $id
     */
    public function ignore($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

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

        $this->addFlash(
            'success',
            $this->translator->trans('follower_requests.follower_requests.ignore.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.follower_requests.ignore',
            'A user follow was ignored',
            $userFollower->toArray()
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.follower_requests');
    }
}
