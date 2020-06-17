<?php

namespace App\Controller\Users;

use App\Entity\User;
use App\Entity\UserBlock;
use App\Entity\UserFollower;
use App\Manager\UserActionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractUsersController.
 */
class AbstractUsersController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var ParameterBagInterface
     */
    protected $params;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserActionManager
     */
    protected $userActionManager;

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

    /* Helpers */

    /**
     * @throws NotFoundHttpException
     */
    protected function _get(string $username): User
    {
        /** @var User|null $user */
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            throw $this->createNotFoundException($this->translator->trans('user_not_found', [], 'users'));
        }

        return $user;
    }

    protected function _canViewDetails($user, $userMyself = null): bool
    {
        if (!$userMyself) {
            $userMyself = $this->getUser();
        }

        if (
            $userMyself &&
            $user === $userMyself
        ) {
            return true;
        }

        /** @var UserBlock|null $userBlock */
        $userBlock = $this->em
            ->getRepository(UserBlock::class)
            ->findOneBy([
                'user' => $user,
                'userBlocked' => $userMyself,
            ])
        ;
        if ($userBlock) {
            return false;
        }

        if (!$user->getPrivate()) {
            return true;
        }

        /** @var UserFollower|null $userFollower */
        $userFollower = $this->em
            ->getRepository(UserFollower::class)
            ->findOneBy([
                'user' => $user,
                'userFollowing' => $userMyself,
                'status' => UserFollower::STATUS_APPROVED,
            ])
        ;
        if ($userFollower) {
            return true;
        }

        return false;
    }
}
