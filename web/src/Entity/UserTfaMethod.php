<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTfaMethodRepository")
 * @ORM\Table(name="user_tfa_methods")
 */
class UserTfaMethod implements Interfaces\StatusInterface
{
    use Traits\TimestampsTrait;

    const METHOD_EMAIL = 'email';
    const METHOD_AUTHENTICATOR = 'authenticator';
    const METHOD_RECOVERY_CODES = 'recovery_codes';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $method;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $data = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userTfaMethods")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser() . ' @ ' . $this->getMethod();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function isEnabled(): bool
    {
        return $this->getEnabled();
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

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
            'provider' => $this->getProvider(),
            'provider_id' => $this->getProviderId(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
