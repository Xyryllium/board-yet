<?php

namespace App\Application\Auth\Services;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\ValueObjects\SessionData;
use App\Infrastructure\Persistence\AuthRepository;

class SessionService
{
    public function __construct(
        private AuthRepository $authRepository
    ) {
    }

    public function createSession(AuthenticatedUser $authenticatedUser, int $expirationDays = 7): SessionData
    {
        $token = $this->authRepository->createTokenWithExpiration(
            $authenticatedUser->userId,
            $authenticatedUser->role?->role,
            $authenticatedUser->role?->organizationId,
            $expirationDays
        );

        return new SessionData(
            token: $token,
            authenticatedUser: $authenticatedUser,
            expirationDays: $expirationDays
        );
    }

    public function revokeSession(string $token): bool
    {
        return $this->authRepository->revokeToken($token);
    }

    public function revokeAllUserSessions(int $userId): bool
    {
        return $this->authRepository->revokeAllUserTokens($userId);
    }

    public function getSessionFromToken(string $token): ?AuthenticatedUser
    {
        return $this->authRepository->findUserByToken($token);
    }
}
