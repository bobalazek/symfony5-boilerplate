<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTfaEmailRepository")
 * @ORM\Table(name="user_tfa_emails")
 */
class UserTfaEmail implements Interfaces\StatusInterface
{
    use Traits\TimestampsTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $usedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userTfaEmails")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser() . ' @ ' . $this->getToken();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeInterface
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeInterface $usedAt): self
    {
        $this->usedAt = $usedAt;

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
            'token' => $this->getToken(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
