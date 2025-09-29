<?php

namespace App\Domain\PasswordReset\Repositories;

interface PasswordResetRepositoryInterface
{
    public function createOrUpdate(string $email, string $token): void;

    public function findByEmail(string $email): ?array;

    public function deleteByEmail(string $email): void;
}
