<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserTfaRecoveryCodeRepository")
 * @ORM\Table(name="user_tfa_recovery_codes")
 */
class UserTfaRecoveryCode implements Interfaces\StatusInterface, Interfaces\ArrayInterface, TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $recoveryCode;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $usedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="userTfaRecoveryCodes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    public function __toString()
    {
        return $this->getUser() . ' @ ' . $this->getRecoveryCode();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecoveryCode(): ?string
    {
        return $this->recoveryCode;
    }

    public function setRecoveryCode(string $recoveryCode): self
    {
        $this->recoveryCode = $recoveryCode;

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
            'recovery_code' => $this->getRecoveryCode(),
            'created_at' => $this->getCreatedAt()
                ? $this->getCreatedAt()->format(DATE_ATOM)
                : null,
        ];
    }
}
