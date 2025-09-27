<?php

namespace App\Domain\Auth\ValueObjects;

use App\Domain\Auth\Entities\AuthenticatedUser;

class SessionData
{
    public function __construct(
        public readonly Token $token,
        public readonly AuthenticatedUser $authenticatedUser,
        public readonly int $expirationDays
    ) {
    }


    public function getResponseData(): array
    {
        return [
            'message' => 'Login successful',
            'user' => [
                'id' => $this->authenticatedUser->userId,
                'name' => $this->authenticatedUser->name,
                'email' => $this->authenticatedUser->email,
                'role' => $this->authenticatedUser->role?->role,
                'organization_id' => $this->authenticatedUser->role?->organizationId,
                'subdomain' => $this->authenticatedUser->subdomain,
            ],
            'token' => $this->token->plainTextToken
        ];
    }
}
