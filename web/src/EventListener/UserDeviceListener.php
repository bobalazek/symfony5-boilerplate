<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserDevice;
use App\Manager\UserDeviceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserDeviceListener.
 */
class UserDeviceListener
{
    /**
     * @param EntityManagerInterface $em
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserDeviceManager
     */
    private $userDeviceManager;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        UserDeviceManager $userDeviceManager
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->userDeviceManager = $userDeviceManager;
    }

    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $token = $this->tokenStorage->getToken();

        if (
            !$token ||
            !$event->isMasterRequest()
        ) {
            return;
        }

        $user = $token->getUser();
        if (!($user instanceof User)) {
            return;
        }

        // User device - last active
        $userDevice = $this->userDeviceManager->get($user, $request);
        $userDevice->setLastActiveAt(new \Datetime());

        $this->em->persist($userDevice);
        $this->em->flush();
    }

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $user = $token->getUser();
        if (
            null === $user ||
            !($user instanceof User)
        ) {
            return;
        }

        $cookieLifetime = 315569520; // 10 years
        $cookieName = UserDevice::UUID_COOKIE_NAME_PREFIX . $user->getId();

        $deviceUuid = $request->attributes->get($cookieName);
        if (null === $deviceUuid) {
            return;
        }

        $cookie = new Cookie(
            $cookieName,
            $deviceUuid,
            time() + $cookieLifetime
        );
        $response->headers->setCookie($cookie);
    }
}
