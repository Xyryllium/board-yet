<?php

namespace App\Domain\User\Entities;

class User
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public ?int $userId = null,
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


    public function toArray(): array
    {
        return [
            'id'    => $this->userId,
            'name'  => $this->name,
            'email' => $this->email,
        ];
    }
}
