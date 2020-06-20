<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\Mailer\DataCollector\MessageDataCollector;

/**
 * @internal
 * @coversNothing
 */
class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var KernelBrowser
     */
    protected $client;

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

    public function getSentEmailMessages()
    {
        $messages = [];

        /** @var MessageDataCollector $mailerCollector */
        $mailerCollector = $this->client->getProfile()->getCollector('mailer');

        $messageEvents = $mailerCollector->getEvents();
        $messageEventsEvents = $messageEvents->getEvents();
        foreach ($messageEventsEvents as $messageEventsEvent) {
            if ($messageEventsEvent->isQueued()) {
                continue;
            }

            $messages[] = $messageEventsEvent->getMessage();
        }

        return $messages;
    }
}
