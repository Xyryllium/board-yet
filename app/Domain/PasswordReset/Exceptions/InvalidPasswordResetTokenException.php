<?php

namespace App\Domain\PasswordReset\Exceptions;

use Exception;

class InvalidPasswordResetTokenException extends Exception
{
    public static function notFound(): self
    {
        return new self('Invalid password reset token');
    }

    public static function invalid(): self
    {
        return new self('Invalid password reset token');
    }
}
