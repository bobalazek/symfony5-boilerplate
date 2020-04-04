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

    protected function loginAs($username)
    {
        $session = self::$container->get('session');

        $firewallName = 'main';
        $firewallContext = 'main';

        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername($username)
        ;

        $token = new PostAuthenticationGuardToken($user, $firewallName, $user->getRoles());

        $session->set('_security_' . $firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}
