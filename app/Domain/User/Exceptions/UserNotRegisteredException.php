<?php

namespace App\Domain\User\Exceptions;

use Exception;

class UserNotRegisteredException extends Exception
{
    protected $message = 'User is not registered.';
    public function __construct(public string $email, public string $token,)
    {
        parent::__construct($this->message);
    }
}
