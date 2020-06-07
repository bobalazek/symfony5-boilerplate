<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserDeviceRepository")
 * @ORM\Table(name="user_devices")
 */
class UserDevice implements Interfaces\ArrayInterface
{
    use Traits\TimestampsTrait;
    use Traits\RequestMetaTrait;

    const UUID_COOKIE_NAME_PREFIX = 'device_uuid_';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="uuid", type="string", length=255, unique=true)
     */
    private $uuid;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="trusted", type="boolean")
     */
    private $trusted = false;

    /**
     * @ORM\Column(name="invalidated", type="boolean")
     */
    private $invalidated = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastActiveAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userDevices")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUuid();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrusted(): bool
    {
        return $this->trusted;
    }

    public function isTrusted(): bool
    {
        return $this->getTrusted();
    }

    public function setTrusted(bool $trusted): self
    {
        $this->trusted = $trusted;

        return $this;
    }

    public function getInvalidated(): bool
    {
        return $this->invalidated;
    }

    public function isInvalidated(): bool
    {
        return $this->getInvalidated();
    }

    public function setInvalidated(bool $invalidated): self
    {
        $this->invalidated = $invalidated;

        return $this;
    }

    public function getLastActiveAt(): ?\DateTimeInterface
    {
        return $this->lastActiveAt;
    }

    public function setLastActiveAt(?\DateTimeInterface $lastActiveAt): self
    {
        $this->lastActiveAt = $lastActiveAt;

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
            'uuid' => $this->getUuid(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
