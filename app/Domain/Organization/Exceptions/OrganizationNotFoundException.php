<?php

namespace App\Domain\Organization\Exceptions;

use Exception;

class OrganizationNotFoundException extends Exception
{
    public function __construct(string $message = "Organization not found", int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}