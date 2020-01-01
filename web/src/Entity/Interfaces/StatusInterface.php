<?php

namespace App\Entity\Interfaces;

/**
 * Interface StatusInterface.
 */
interface StatusInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_IGNORED = 'ignored';
}
