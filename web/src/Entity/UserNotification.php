<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserNotificationRepository")
 * @ORM\Table(name="user_notifications")
 */
class UserNotification implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    const TYPE_USER_FOLLOW = 'user.follow'; // When a new user follows you
    const TYPE_USER_FOLLOW_REQUEST = 'user.follow.request'; // When a new user requests to follow you

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $type;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $seenAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $readAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userNotifications")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser() . ' @ ' . $this->getType();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getSeenAt(): ?\DateTimeInterface
    {
        return $this->seenAt;
    }

    public function setSeenAt(?\DateTimeInterface $seenAt): self
    {
        $this->seenAt = $seenAt;

        return $this;
    }

    public function isSeen(): bool
    {
        return null !== $this->getSeenAt();
    }

    public function getReadAt(): ?\DateTimeInterface
    {
        return $this->readAt;
    }

    public function setReadAt(?\DateTimeInterface $readAt): self
    {
        $this->readAt = $readAt;

        return $this;
    }

    public function isRead(): bool
    {
        return null !== $this->getReadAt();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'data' => $this->getData(),
            'user_id' => $this->getUser()->getId(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
