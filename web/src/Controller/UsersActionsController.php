<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Entity\UserNotification;
use App\Manager\UserNotificationManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersActionsController.
 */
class UsersActionsController extends AbstractUsersController
{
    /**
     * @Route("/users/{username}/follow", name="users.follow")
     *
     * @param mixed $username
     */
    public function follow(
        $username,
        Request $request,
        UserNotificationManager $userNotificationManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userMyself = $this->getUser();

        $user = $this->em->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('follow.flash.user_does_not_exist', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        if ($userMyself === $user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('follow.flash.can_not_follow_yourself', [], 'users')
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $user->getUsername(),
            ]);
        }

        $userFollower = $this->em->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $user,
                'userFollowing' => $userMyself,
            ])
        ;
        if ($userFollower) {
            $text = UserFollower::STATUS_PENDING === $userFollower->getStatus()
                ? $this->translator->trans('follow.flash.request_pending', [], 'users')
                : $this->translator->trans('follow.flash.already_following_this_user', [], 'users');
            $this->addFlash(
                'danger',
                $text
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $user->getUsername(),
            ]);
        }

        $isPendingRequest = $user->getPrivate();

        $userFollower = new UserFollower();
        $userFollower
            ->setUser($user)
            ->setUserFollowing($userMyself)
            ->setStatus(
                $isPendingRequest
                    ? UserFollower::STATUS_PENDING
                    : UserFollower::STATUS_APPROVED
            )
        ;

        $this->em->persist($userFollower);
        $this->em->flush();

        $text = $isPendingRequest
            ? $this->translator->trans('follow.flash.request_success', [], 'users')
            : $this->translator->trans('follow.flash.success', [], 'users');
        $this->addFlash(
            'success',
            $text
        );

        $this->userActionManager->add(
            'users.follow',
            'The user followed a user',
            [
                'id' => $user->getId(),
            ]
        );

        $userNotificationManager->add(
            $isPendingRequest
                ? UserNotification::TYPE_USER_FOLLOW_REQUEST
                : UserNotification::TYPE_USER_FOLLOW,
            [
                'user_id' => $userMyself->getId(),
            ],
            $user
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.detail', [
            'username' => $user->getUsername(),
        ]);
    }

    /**
     * @Route("/users/{username}/unfollow", name="users.unfollow")
     *
     * @param mixed $username
     */
    public function unfollow($username, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $userToUnfollow = $this->em->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$userToUnfollow) {
            $this->addFlash(
                'danger',
                $this->translator->trans('unfollow.flash.user_does_not_exist', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        $userFollower = $this->em->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $userToUnfollow,
                'userFollowing' => $user,
            ])
        ;
        if (!$userFollower) {
            $this->addFlash(
                'danger',
                $this->translator->trans('unfollow.flash.you_are_not_following_this_user', [], 'users')
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $userToUnfollow->getUsername(),
            ]);
        }

        $this->em->remove($userFollower);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('unfollow.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.unfollow',
            'The user unfollowed a user',
            [
                'id' => $userToUnfollow->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.detail', [
            'username' => $userToUnfollow->getUsername(),
        ]);
    }

    /**
     * @Route("/users/{username}/block", name="users.block")
     *
     * @param mixed $username
     */
    public function block($username, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $userMyself = $this->getUser();

        $user = $this->em->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('block.flash.user_does_not_exist', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        if ($userMyself === $user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('block.flash.can_not_block_yourself', [], 'users')
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $user->getUsername(),
            ]);
        }

        $userBlock = $this->em->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $userMyself,
                'userBlocked' => $user,
            ])
        ;
        if ($userBlock) {
            $this->addFlash(
                'danger',
                $this->translator->trans('block.flash.already_blocking_this_user', [], 'users')
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $user->getUsername(),
            ]);
        }

        $userBlock = new UserBlock();
        $userBlock
            ->setUser($userMyself)
            ->setUserBlocked($user)
        ;

        $this->em->persist($userBlock);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('block.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.block',
            'The user blocked a user',
            [
                'id' => $user->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.detail', [
            'username' => $user->getUsername(),
        ]);
    }

    /**
     * @Route("/users/{username}/unblock", name="users.unblock")
     *
     * @param mixed $username
     */
    public function unblock($username, Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $userToUnblock = $this->em->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$userToUnblock) {
            $this->addFlash(
                'danger',
                $this->translator->trans('unblock.flash.user_does_not_exist', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        $userBlock = $this->em->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $userToUnblock,
            ])
        ;
        if (!$userBlock) {
            $this->addFlash(
                'danger',
                $this->translator->trans('unblock.flash.you_are_not_blocking_this_user', [], 'users')
            );

            return $this->redirectToRoute('users.detail', [
                'username' => $userToUnblock->getUsername(),
            ]);
        }

        $this->em->remove($userBlock);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('unblock.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.unblock',
            'The user unblocked a user',
            [
                'id' => $userToUnblock->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users.detail', [
            'username' => $userToUnblock->getUsername(),
        ]);
    }

    /**
     * @Route("/users/{username}/lock", name="users.lock")
     *
     * @param mixed $username
     */
    public function lock($username, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_MODERATOR');

        $user = $this->_get($username);

        if ($user === $this->getUser()) {
            throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
        }

        $reason = $request->query->get('reason', 'The user was locked.');

        $user
            ->setLocked(true)
            ->setLockedReason($reason)
        ;

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('lock.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.lock',
            'A user was locked',
            [
                'id' => $user->getId(),
                'reason' => $reason,
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/users/{username}/unlock", name="users.unlock")
     *
     * @param mixed $username
     */
    public function unlock($username, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_MODERATOR');

        $user = $this->_get($username);

        $user
            ->setLocked(false)
            ->setLockedReason(null)
        ;

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('unlock.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.unlock',
            'A user was unlocked',
            [
                'id' => $user->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/users/{username}/delete", name="users.delete")
     *
     * @param mixed $username
     */
    public function delete($username, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_MODERATOR');

        $user = $this->_get($username);

        if ($user === $this->getUser()) {
            throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
        }

        if (null !== $user->getDeletedAt()) {
            // If it's already deleted and we would accidentially delete it again,
            //   like pressing twice on the delete link, it would hard delete the product,
            //   which (at the moment) we do not want.
            throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
        }

        $this->em->remove($user);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('delete.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.delete',
            'A user was deleted',
            [
                'id' => $user->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users');
    }

    /**
     * @Route("/users/{username}/undelete", name="users.undelete")
     *
     * @param mixed $username
     */
    public function undelete($username, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER_MODERATOR');

        $user = $this->_get($username);

        $user->setDeletedAt(null);

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash(
            'success',
            $this->translator->trans('undelete.flash.success', [], 'users')
        );

        $this->userActionManager->add(
            'users.undelete',
            'A user was undeleted',
            [
                'id' => $user->getId(),
            ]
        );

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('users');
    }
}
