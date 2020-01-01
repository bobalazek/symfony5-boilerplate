<?php

namespace App\Message;

/**
 * Class UserExportRequest.
 */
class UserExportRequest
{
    private $userExportId;

    /**
     * UserExportRequest constructor.
     */
    public function __construct(int $userExportId)
    {
        $this->userExportId = $userExportId;
    }

    public function getUserExportId(): int
    {
        return $this->userExportId;
    }
}
