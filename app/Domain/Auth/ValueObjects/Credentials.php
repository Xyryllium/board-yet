<?php

namespace App\Domain\Auth\ValueObjects;

class Credentials
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {
    }

    public function isValid(): bool
    {
        return !empty($this->email) && !empty($this->password);
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
