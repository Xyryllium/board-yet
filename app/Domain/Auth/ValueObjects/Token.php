<?php

namespace App\Domain\Auth\ValueObjects;

class Token
{
    public function __construct(
        public readonly string $plainTextToken,
        public readonly ?string $role = null,
        public readonly ?int $organizationId = null,
        public readonly ?int $userId = null,
    ) {
    }

    public function hasRole(): bool
    {
        return $this->role !== null;
    }

    public function hasOrganization(): bool
    {
        return $this->organizationId !== null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMember(): bool
    {
        return $this->role === 'member';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function toArray(): array
    {
        return [
            'token' => $this->plainTextToken,
            'role' => $this->role,
            'organization_id' => $this->organizationId,
            'user_id' => $this->userId,
        ];
    }
}
