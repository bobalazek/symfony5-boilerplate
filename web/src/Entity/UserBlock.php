<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserBlockRepository")
 * @ORM\Table(name="user_blocks")
 */
class UserBlock implements Interfaces\StatusInterface
{
    use Traits\TimestampsTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userBlocks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userBlocked")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $userBlocked;

    public function __toString()
    {
        return $this->getUser() . ' blocks ' . $this->getUserBlocked();
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

    public function getUserBlocked(): ?User
    {
        return $this->userBlocked;
    }

    public function setUserBlocked(?User $userBlocked): self
    {
        $this->userBlocked = $userBlocked;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUser()->getId(),
            'user_blocked_id' => $this->getUserBlocked()->getId(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
