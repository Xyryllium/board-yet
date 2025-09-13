<?php

namespace App\Domain\Auth\Repositories;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\ValueObjects\Credentials;
use App\Domain\Auth\ValueObjects\Token;
use App\Domain\User\Entities\User;

interface AuthRepositoryInterface
{
    public function authenticate(Credentials $credentials): ?User;

    public function createToken(User $user, ?string $role = null, ?int $organizationId = null): Token;

    public function revokeToken(string $token): bool;

    public function revokeAllUserTokens(int $userId): bool;

    public function findUserByToken(string $token): ?AuthenticatedUser;
}
