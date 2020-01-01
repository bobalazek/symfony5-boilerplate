<?php

namespace App\Entity\Traits;

use Cocur\Slugify\Slugify;

/**
 * Trait IdSlugTrait.
 */
trait IdSlugTrait
{
    public function getIdSlug()
    {
        $slugify = new Slugify();

        return $this->getId() . '-' . $slugify->slugify($this->getName());
    }
}
