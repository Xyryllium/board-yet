<?php

namespace App\Domain\User\Entities;

class User
{
    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?int $userId = null,
        public bool $emailVerified = false,
    ) {
    }

    public function getId(): int
    {
        return $this->userId;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getEmail(): string
    {
        return $this->email;
    }


    public function getPassword(): string
    {
        return $this->password;
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->emailVerified;
    }


    public function toArray(): array
    {
        return [
            'id'    => $this->userId,
            'name'  => $this->name,
            'email' => $this->email,
            'email_verified' => $this->emailVerified,
        ];
    }
}
