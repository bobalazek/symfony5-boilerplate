<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="sessions")
 */
class Session
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="sess_id", type="string", length=128)
     */
    private $sessId;

    /**
     * @ORM\Column(name="sess_data", type="blob")
     */
    private $sessData;

    /**
     * @ORM\Column(name="sess_time", type="integer")
     */
    private $sessTime;

    /**
     * @ORM\Column(name="sess_lifetime", type="integer")
     */
    private $sessLifetime;
}
