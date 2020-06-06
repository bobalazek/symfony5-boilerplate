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

        // If the current user device was invalidated,
        // then log out the user and remove the user device
        $userDevice = $this->userDeviceManager->get($user, $request);
        if ($userDevice->isInvalidated()) {
            $this->tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            $this->em->remove($userDevice);
            $this->em->flush();

            return;
        }

        // Now we can update out current device
        $this->userDeviceManager->update($user, $request);
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

        // This will only be set the first time a new user device was created.
        // Check the UserDeviceManager::create() method
        $deviceUuid = $request->attributes->get($cookieName);
        if ($deviceUuid) {
            $cookie = new Cookie(
                $cookieName,
                $deviceUuid,
                time() + $cookieLifetime
            );
            $response->headers->setCookie($cookie);

            return;
        }
    }
}
