<?php

namespace App\Domain\Auth\Entities;

use App\Domain\Auth\ValueObjects\Token;
use App\Domain\Auth\ValueObjects\UserRole;

class AuthenticatedUser
{
    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $email,
        public readonly Token $token,
        public readonly ?UserRole $role = null,
        public readonly ?string $subdomain = null,
        public readonly bool $emailVerified = false,
    ) {
    }

    public function hasRole(): bool
    {
        return $this->role !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() ?? false;
    }

    public function isMember(): bool
    {
        return $this->role?->isMember() ?? false;
    }

    public function isOwner(): bool
    {
        return $this->role?->isOwner() ?? false;
    }

    public function hasPermission(string $permission): bool
    {
        return $this->role?->hasPermission($permission) ?? false;
    }

    public function getOrganizationId(): ?int
    {
        return $this->role?->organizationId;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->userId,
                'name' => $this->name,
                'email' => $this->email,
                'role' => $this->role?->role,
                'organization_id' => $this->role?->organizationId,
                'subdomain' => $this->subdomain,
                'email_verified' => $this->emailVerified,
            ],
            'token' => $this->token->plainTextToken,
        ];
    }
}
