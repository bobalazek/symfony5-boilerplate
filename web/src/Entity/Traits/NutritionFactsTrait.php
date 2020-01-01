<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait NutritionFactsTrait.
 */
trait NutritionFactsTrait
{
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $nutritionFacts = [];

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $nutritionFactsNote;

    public function getNutritionFacts(): ?array
    {
        return $this->nutritionFacts;
    }

    public function setNutritionFacts(?array $nutritionFacts): self
    {
        $this->nutritionFacts = $nutritionFacts;

        return $this;
    }

    public function getNutritionFactsNote(): ?string
    {
        return $this->nutritionFactsNote;
    }

    public function setNutritionFactsNote(?string $nutritionFactsNote): self
    {
        $this->nutritionFactsNote = $nutritionFactsNote;

        return $this;
    }
}
