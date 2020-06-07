<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Entity\UserFollower;
use App\Manager\UserManager;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class UsersController.
 */
class UsersController extends AbstractUsersController
{
    /**
     * @Route("/users", name="users")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER_MODERATOR');

        $search = $request->get('search', '');
        $status = $request->query->get('status', 'active');

        $queryBuilder = $this->em
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
        ;

        if ($search) {
            $queryBuilder
                ->where($queryBuilder->expr()->orX(
                    $queryBuilder->expr()->like('u.name', ':search'),
                    $queryBuilder->expr()->like('u.username', ':search'),
                    $queryBuilder->expr()->like('u.email', ':search')
                ))
                ->setParameter('search', '%' . $search . '%')
            ;
        }

        if ('deleted' === $status) {
            $this->em->getFilters()->disable('gedmo_softdeletable');
            $queryBuilder = $queryBuilder
                ->andWhere('u.deletedAt IS NOT NULL')
            ;
        } elseif ('locked' === $status) {
            $queryBuilder = $queryBuilder
                ->andWhere('u.locked = :locked')
                ->setParameter('locked', true)
            ;
        } else {
            $queryBuilder = $queryBuilder
                ->andWhere('u.locked = :locked')
                ->setParameter('locked', false)
            ;
        }

        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('contents/users/index.html.twig', [
            'pagination' => $pagination,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * @Route("/users/{username}", name="users.detail")
     *
     * @param mixed $username
     */
    public function detail($username, UserManager $userManager): Response
    {
        $userMyself = $this->getUser();

        $user = 'me' === $username
            ? $userMyself
            : $this->em->getRepository(User::class)->findOneByUsername($username);
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user_not_found', [], 'users'));
        }

        if ($user->isLocked()) {
            throw $this->createNotFoundException($this->translator->trans('detail.user_is_locked', [], 'users'));
        }

        $userFollower = null;
        $canViewDetails = $this->_canViewDetails($user, $userMyself);
        $canFollow = false;
        $canUnfollow = false;
        $canBlock = false;
        $canUnblock = false;
        $isBlockedByUser = false;
        $isBlockingUser = false;

        if (
            $userMyself &&
            $user !== $userMyself
        ) {
            $isFollowing = $userManager->isFollowing($userMyself, $user);
            if ($isFollowing) {
                $canUnfollow = true;
            } else {
                $canFollow = true;
            }

            $isBlocking = $userManager->isBlocking($userMyself, $user);
            if ($isBlocking) {
                $canUnblock = true;
                $isBlockingUser = true;
            } else {
                $canBlock = true;
            }

            $isBlockedByUser = $userManager->isBlockedBy($userMyself, $user);
        }

        $followersCount = $userManager->followersCount($user);
        $followingCount = $userManager->followingCount($user);
        $points = $userManager->pointsCount($user);

        return $this->render('contents/users/detail.html.twig', [
            'user' => $user,
            'user_follower' => $userFollower,
            'can_follow' => $canFollow,
            'can_unfollow' => $canUnfollow,
            'can_block' => $canBlock,
            'can_unblock' => $canUnblock,
            'is_blocked_by_user' => $isBlockedByUser,
            'is_blocking_user' => $isBlockingUser,
            'can_view_details' => $canViewDetails,
            'followers_count' => $followersCount,
            'following_count' => $followingCount,
            'points' => $points,
        ]);
    }

    /**
     * @Route("/users/{username}/followers", name="users.followers")
     *
     * @param mixed $username
     */
    public function followers($username, Request $request, PaginatorInterface $paginator)
    {
        $user = $this->em->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('user_not_found', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        if (!$this->_canViewDetails($user)) {
            throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
        }

        $query = $this->em->getRepository(UserFollower::class)
            ->findBy([
                'user' => $user,
                'status' => UserFollower::STATUS_APPROVED,
            ], [
                'createdAt' => 'DESC',
            ])
        ;
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('contents/users/followers.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/users/{username}/following", name="users.following")
     *
     * @param mixed $username
     */
    public function following($username, Request $request, PaginatorInterface $paginator)
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            $this->addFlash(
                'danger',
                $this->translator->trans('user_not_found', [], 'users')
            );

            return $this->redirectToRoute('home');
        }

        if (!$this->_canViewDetails($user)) {
            throw $this->createAccessDeniedException($this->translator->trans('not_allowed'));
        }

        $query = $this->em->getRepository(UserFollower::class)
            ->findBy([
                'userFollowing' => $user,
            ], [
                'createdAt' => 'DESC',
            ])
        ;
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            12
        );

        return $this->render('contents/users/following.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
        ]);
    }
}
