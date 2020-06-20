<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserAction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserActionManager.
 */
class UserActionManager
{
    /**
     * @var Security
     */
    private $security;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        RequestStack $requestStack
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    /**
     * @param string $key
     * @param string $message
     * @param User   $user
     *
     * @return bool
     */
    public function add(
        $key,
        $message,
        array $data = [],
        User $user = null
    ) {
        if (!$user) {
            $user = $this->security->getUser();
        }

        $request = $this->requestStack->getCurrentRequest();

        $userAction = new UserAction();
        $userAction
            ->setKey($key)
            ->setMessage($message)
            ->setData($data)
            ->setIp($request->getClientIp())
            ->setUserAgent($request->headers->get('User-Agent'))
            ->setSessionId($request->getSession()->getId())
            ->setUser($user)
        ;

        $this->em->persist($userAction);
        $this->em->flush();

        return true;
    }
}
