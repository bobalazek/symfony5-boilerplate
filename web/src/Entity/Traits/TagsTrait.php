<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait TagsTrait.
 */
trait TagsTrait
{
    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $tags = [];

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        if ($tags) {
            $tags = array_map('strtolower', $tags);
        }

        $this->tags = $tags;

        return $this;
    }
}
