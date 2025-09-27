<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class TokenCreationException extends Exception
{
    public function __construct(string $message = 'Failed to create authentication token')
    {
        parent::__construct($message);
    }
}
