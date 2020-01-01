<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait LocaleTrait.
 */
trait LocaleTrait
{
    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $locale;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setLocale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }
}
