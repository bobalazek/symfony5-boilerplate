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

        $uuid = $request->cookies->get($cookieName);
        $userDevice = $this->em
            ->getRepository(UserDevice::class)
            ->findOneByUuid($uuid)
        ;

        if (null === $userDevice) {
            $userDevice = $this->create($user, $request, $uuid);
        }

        $this->currentUserDevice = $userDevice;

        return $userDevice;
    }

    /**
     * Creates a user device.
     *
     * @param sting $uuid
     *
     * @return UserDevice
     */
    public function create(User $user, Request $request, $uuid = null)
    {
        $session = $request->getSession();

        if (!$uuid) {
            $uuid = Uuid::uuid4();
        }

        $userAgent = $request->headers->get('User-Agent');
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        $userDevice = new UserDevice();
        $userDevice
            ->setUuid($uuid)
            ->setName($agent->platform() . ' - ' . $agent->browser())
            ->setUser($user)
        ;

        $cookieName = UserDevice::UUID_COOKIE_NAME_PREFIX . $user->getId();

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
