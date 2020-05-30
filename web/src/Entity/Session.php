<?php

namespace CoreBundle\Entity;

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
    protected $sessId;

    /**
     * @ORM\Column(name="sess_data", type="blob")
     */
    protected $sessData;

    /**
     * @ORM\Column(name="sess_time", type="integer")
     */
    protected $sessTime;

    /**
     * @ORM\Column(name="sess_lifetime", type="integer")
     */
    protected $sessLifetime;
}
