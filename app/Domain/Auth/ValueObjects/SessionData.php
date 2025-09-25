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

    public function getCookieData(): array
    {
        return [
            'name' => 'api_token',
            'value' => $this->token->plainTextToken,
            'minutes' => 60 * 24 * $this->expirationDays,
            'path' => '/',
            'domain' => config('app.cookie_domain', 'api-test-board.com'),
            'secure' => config('app.secure_cookies', false),
            'httpOnly' => true,
            'sameSite' => 'lax'
        ];
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
