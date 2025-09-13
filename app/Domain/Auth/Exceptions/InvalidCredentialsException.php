<?php

namespace App\Domain\Auth\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    public function __construct(string $message = 'Invalid credentials provided')
    {
        parent::__construct($message);
    }
}
