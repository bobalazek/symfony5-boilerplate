<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserFollowerRepository")
 * @ORM\Table(name="user_followers")
 */
class UserFollower implements Interfaces\StatusInterface
{
    use Traits\StatusTrait;
    use Traits\TimestampsTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

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
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
