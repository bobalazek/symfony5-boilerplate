<?php

namespace App\MessageHandler;

use App\Entity\ThreadUserMessage;
use App\Message\ThreadUserMessage as ThreadUserMessageMessage;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Class ThreadUserMessageHandler.
 */
class ThreadUserMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke(ThreadUserMessageMessage $threadUserMessageMessage)
    {
        $threadUserMessage = $this->em
            ->getRepository(ThreadUserMessage::class)
            ->findOneById($threadUserMessageMessage->getThreadUserMessageId())
        ;

        if (!$threadUserMessage) {
            throw new UnrecoverableMessageHandlingException();
        }

        // TODO: send to WS
    }
}
