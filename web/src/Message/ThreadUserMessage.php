<?php

namespace App\Message;

/**
 * Class ThreadUserMessage.
 */
class ThreadUserMessage
{
    private $threadUserMessageId;

    /**
     * UserExportRequest constructor.
     */
    public function __construct(int $threadUserMessageId)
    {
        $this->threadUserMessageId = $threadUserMessageId;
    }

    public function getThreadUserMessageId(): int
    {
        return $this->threadUserMessageId;
    }
}
