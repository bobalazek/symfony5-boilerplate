<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait StatusTrait.
 */
trait StatusTrait
{
    /**
     * @ORM\Column(type="string", length=16)
     */
    private $status;

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

    public function getStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_PENDING,
            self::STATUS_IGNORED,
        ];
    }
}
