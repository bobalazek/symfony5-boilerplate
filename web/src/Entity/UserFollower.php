<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ORM\UserFollowerRepository")
 * @ORM\Table(name="user_followers")
 */
class UserFollower implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IGNORED = 'ignored';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userFollowers")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userFollowing")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $userFollowing;

    public function __toString()
    {
        return $this->getUser() . ' follows ' . $this->getUserFollowing();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

        public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isStatusPending(): bool
    {
        return self::STATUS_PENDING === $this->getStatus();
    }

    public function isStatusApproved(): bool
    {
        return self::STATUS_APPROVED === $this->getStatus();
    }

    public function isStatusIgnored(): bool
    {
        return self::STATUS_IGNORED === $this->getStatus();
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

    public function getUserFollowing(): ?User
    {
        return $this->userFollowing;
    }

    public function setUserFollowing(?User $userFollowing): self
    {
        $this->userFollowing = $userFollowing;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUser()->getId(),
            'user_following_id' => $this->getUserFollowing()->getId(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
