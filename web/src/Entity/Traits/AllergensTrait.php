<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait AllergensTrait.
 */
trait AllergensTrait
{
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $allergens = [];

    public function getAllergens(): ?array
    {
        return $this->allergens;
    }

    public function setAllergens(?array $allergens): self
    {
        if ($allergens) {
            $allergens = array_map('strtolower', $allergens);
        }

        $this->allergens = $allergens;

        return $this;
    }
}
