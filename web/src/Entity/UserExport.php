<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserExportRepository")
 * @ORM\Table(name="user_exports")
 */
class UserExport implements Interfaces\ArrayInterface
{
    use Traits\TimestampsTrait;

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="status", type="string", length=16)
     */
    private $status;

    /**
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $failedMessage;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fileKey;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $fileUrl;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $failedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiresAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userExports")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser()->getUsername() . ' @ ' . $this->getStatus();
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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getFailedMessage(): ?string
    {
        return $this->failedMessage;
    }

    public function setFailedMessage(?string $failedMessage): self
    {
        $this->failedMessage = $failedMessage;

        return $this;
    }

    public function getFileKey(): ?string
    {
        return $this->fileKey;
    }

    public function setFileKey(?string $fileKey): self
    {
        $this->fileKey = $fileKey;

        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): self
    {
        $this->fileUrl = $fileUrl;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeInterface
    {
        return $this->completedAt;
    }

    public function setCompletedAt(\DateTimeInterface $completedAt): self
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getFailedAt(): ?\DateTimeInterface
    {
        return $this->failedAt;
    }

    public function setFailedAt(\DateTimeInterface $failedAt): self
    {
        $this->failedAt = $failedAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

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
            'status' => $this->getStatus(),
            'user_id' => $this->getUser()->getId(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
