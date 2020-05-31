<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\UserDevice;
use Doctrine\ORM\EntityManagerInterface;
use Jenssegers\Agent\Agent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserDeviceManager.
 */
class UserDeviceManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var UserDevice
     */
    protected $currentUserDevice;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function get(User $user, Request $request)
    {
        if (null !== $this->currentUserDevice) {
            return $this->currentUserDevice;
        }

        $cookieName = UserDevice::UUID_COOKIE_NAME_PREFIX . $user->getId();
        $ip = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');
        $sessionId = $request->getSession()->getId();
        $uuid = $request->cookies->get($cookieName);

        $userDevice = $this->em
            ->getRepository(UserDevice::class)
            ->findOneByUuid($uuid)
        ;
        if (null === $userDevice) {
            $userDevice = $this->create(
                $user,
                $request,
                $cookieName,
                $ip,
                $userAgent,
                $sessionId,
                $uuid
            );
        }

        // Update user device if it was changed
        $userDeviceChanged = false;
        if ($userDevice->getIp() !== $ip) {
            $userDevice->setIp($ip);
            $userDeviceChanged = true;
        }

        if ($userDevice->getUserAgent() !== $userAgent) {
            $userDevice->setUserAgent($userAgent);
            $userDeviceChanged = true;
        }

        if ($userDevice->getSessionId() !== $sessionId) {
            $userDevice->setSessionId($sessionId);
            $userDeviceChanged = true;
        }

        if ($userDeviceChanged) {
            $this->em->persist($userDevice);
            $this->em->flush();
        }

        $this->currentUserDevice = $userDevice;

        return $userDevice;
    }

    /**
     * Create an user device.
     *
     * @return UserDevice
     */
    public function create(
        User $user,
        Request $request,
        string $cookieName,
        string $ip,
        string $userAgent,
        string $sessionId,
        string $uuid = null
    ) {
        $session = $request->getSession();

        if (!$uuid) {
            $uuid = Uuid::uuid4();
        }

        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $platform = $agent->platform();
        $browser = $agent->browser();

        $name = $platform . ' - ' . $browser;

        $userDevice = new UserDevice();
        $userDevice
            ->setUuid($uuid)
            ->setName($name)
            ->setIp($ip)
            ->setUserAgent($userAgent)
            ->setSessionId($sessionId)
            ->setUser($user)
        ;

        // We will use that in UserDeviceListener->onKernelResponse()
        $request->attributes->set(
            $cookieName,
            $userDevice->getUuid()
        );

        return $userDevice;
    }

    /**
     * Is the current device trusted?
     *
     * @return bool
     */
    public function isCurrentTrusted(User $user, Request $request)
    {
        $userDevice = $this->get($user, $request);

        return $userDevice->isTrusted();
    }

    /**
     * Set the current device as trusted.
     *
     * @return bool
     */
    public function setCurrentAsTrusted(User $user, Request $request)
    {
        $userDevice = $this->get($user, $request);

        $userDevice->setTrusted(true);

        $this->em->persist($userDevice);
        $this->em->flush();

        return true;
    }
}
