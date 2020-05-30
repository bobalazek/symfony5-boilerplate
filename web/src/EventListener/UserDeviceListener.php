<?php

namespace App\EventListener;

use App\Entity\User;
use App\Entity\UserDevice;
use App\Manager\UserDeviceManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpFoundation\Cookie;
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
    )
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->userDeviceManager = $userDeviceManager;
    }

    /**
     * @param ControllerEvent $event
     */
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

    /**
     * @param ResponseEvent $event
     */
    public function onKernelResponse(ResponseEvent $event)
    {
        /**
         * Create the the user_device cookie if necessary
         *   (will happen only when a new device is created).
         */
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cookieLifetime = 7776000; // 90 days
        $deviceUuid = $request->attributes->get(UserDevice::UUID_COOKIE_NAME);
        if (null === $deviceUuid) {
            return;
        }

        $cookie = new Cookie(
            UserDevice::UUID_COOKIE_NAME,
            $deviceUuid,
            time() + $cookieLifetime
        );
        $response->headers->setCookie($cookie);
    }
}
