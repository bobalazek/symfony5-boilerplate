<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

/**
 * @internal
 * @coversNothing
 */
class WebTestCase extends SymfonyWebTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = self::$container
            ->get('doctrine')
            ->getManager()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }

    protected function loginAs(string $username, string $firewallContext = 'main')
    {
        $session = self::$container->get('session');

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            throw new \LogicException(sprintf('The user with the username "%s" does not exist.', $username));
        }

        $token = new PostAuthenticationGuardToken($user, $firewallContext, $user->getRoles());

        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $this;
    }
}
