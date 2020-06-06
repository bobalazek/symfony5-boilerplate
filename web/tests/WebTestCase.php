<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;

/**
 * @internal
 * @coversNothing
 */
class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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
    public function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }

    public function loginAs(string $username)
    {
        $user = $this->em
            ->getRepository(User::class)
            ->findOneByUsername($username)
        ;
        if (!$user) {
            throw new \LogicException(sprintf('The user with the username "%s" does not exist.', $username));
        }

        $this->client->loginUser($user);

        return $this;
    }
}
