<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserExportRepository")
 * @ORM\Table(name="user_exports")
 * @Vich\Uploadable()
 */
class UserExport implements Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

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
     * @ORM\Column(type="string", length=16)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $failedMessage;

    /**
     * @Vich\UploadableField(
     *   mapping="user_export",
     *   fileNameProperty="fileEmbedded.name",
     *   size="fileEmbedded.size",
     *   mimeType="fileEmbedded.mimeType",
     *   originalName="fileEmbedded.originalName",
     *   dimensions="fileEmbedded.dimensions"
     * )
     */
    private $file;

    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $fileEmbedded;

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

    public function __construct()
    {
        $this->fileEmbedded = new EmbeddedFile();
    }

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

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getFileEmbedded(): ?EmbeddedFile
    {
        return $this->fileEmbedded;
    }

    public function setFileEmbedded(?EmbeddedFile $fileEmbedded): self
    {
        $this->fileEmbedded = $fileEmbedded;

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
            'created_at' => $this->getCreatedAt()->format(DATE_ATOM),
        ];
    }
}
