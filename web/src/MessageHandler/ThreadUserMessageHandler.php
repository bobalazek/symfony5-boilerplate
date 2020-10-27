<?php

namespace App\MessageHandler;

use App\Entity\ThreadUserMessage;
use App\Manager\WebSocketManager;
use App\Message\ThreadUserMessage as ThreadUserMessageMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Class ThreadUserMessageHandler.
 */
class ThreadUserMessageHandler implements MessageHandlerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var WebSocketManager
     */
    private $webSocketManager;

    public function __construct(
        EntityManagerInterface $em,
        WebSocketManager $webSocketManager
    ) {
        $this->em = $em;
        $this->webSocketManager = $webSocketManager;
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

        $this->webSocketManager->send(
            $threadUserMessage->getThreadUser()->getThread()->getChannel(),
            $threadUserMessage->toArray()
        );
    }
}
