<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTfaMethodRepository")
 * @ORM\Table(name="user_tfa_methods")
 */
class UserTfaMethod implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    const METHOD_EMAIL = 'email';
    const METHOD_GOOGLE_AUTHENTICATOR = 'google_authenticator';
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

    /**
     * Temporary propery used for entering codes for google authenticator and such.
     */
    private $code;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'method' => $this->getMethod(),
            'data' => $this->getData(),
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
