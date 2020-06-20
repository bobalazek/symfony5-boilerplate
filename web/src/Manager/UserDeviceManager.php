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
     * @var UserDevice|null
     */
    protected $currentUserDevice;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Get the user device.
     *
     * @return UserDevice|null
     */
    public function get(
        User $user,
        Request $request,
        bool $returnWithoutCreate = false
    ) {
        // TODO: abstract somewhere
        $cookieName = UserDevice::UUID_COOKIE_NAME_PREFIX . $user->getId();
        $ip = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');
        $sessionId = $request->getSession()->getId();
        $uuid = $request->cookies->get($cookieName);

        if ($this->currentUserDevice) {
            $userDevice = $this->currentUserDevice;
        } else {
            $userDevice = $this->em
                ->getRepository(UserDevice::class)
                ->findOneByUuid($uuid)
            ;
            $this->currentUserDevice = $userDevice;
        }

        if ($returnWithoutCreate) {
            return $userDevice;
        }

        if (!$userDevice) {
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

        $this->currentUserDevice = $userDevice;

        return $userDevice;
    }

    /**
     * Get the user device.
     *
     * @return UserDevice|null
     */
    public function update(
        User $user,
        Request $request
    ) {
        // TODO: abstract somewhere
        $cookieName = UserDevice::UUID_COOKIE_NAME_PREFIX . $user->getId();
        $ip = $request->getClientIp();
        $userAgent = $request->headers->get('User-Agent');
        $sessionId = $request->getSession()->getId();
        $uuid = $request->cookies->get($cookieName);

        $userDevice = $this->get($user, $request);

        if ($userDevice->getIp() !== $ip) {
            $userDevice->setIp($ip);
        }

        if ($userDevice->getUserAgent() !== $userAgent) {
            $userDevice->setUserAgent($userAgent);
        }

        if ($userDevice->getSessionId() !== $sessionId) {
            $userDevice->setSessionId($sessionId);
        }

        $userDevice->setLastActiveAt(new \Datetime());

        $this->em->persist($userDevice);
        $this->em->flush();

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

        $this->em->persist($userDevice);
        $this->em->flush();

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
