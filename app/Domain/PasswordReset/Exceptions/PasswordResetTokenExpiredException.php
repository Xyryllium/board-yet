<?php

namespace App\Domain\PasswordReset\Exceptions;

use Exception;

class PasswordResetTokenExpiredException extends Exception
{
    public static function expired(): self
    {
        return new self('Password reset token has expired');
    }
}
