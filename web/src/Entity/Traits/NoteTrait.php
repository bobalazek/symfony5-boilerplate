<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait NoteTrait.
 */
trait NoteTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }
}
