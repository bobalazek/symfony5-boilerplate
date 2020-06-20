<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ThreadUserMessageRepository")
 * @ORM\Table(name="thread_user_messages")
 */
class ThreadUserMessage implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ThreadUser", inversedBy="threadUserMessages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $threadUser;

    public function __toString()
    {
        return $this->getBody();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getThreadUser(): ?ThreadUser
    {
        return $this->threadUser;
    }

    public function setThreadUser(?ThreadUser $threadUser): self
    {
        $this->threadUser = $threadUser;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'body' => $this->getBody(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
