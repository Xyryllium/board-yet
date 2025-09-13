<?php

namespace App\Domain\Auth\ValueObjects;

class UserRole
{
    public function __construct(
        public readonly string $role,
        public readonly int $organizationId,
    ) {
        $this->validateRole($role);
    }

    private function validateRole(string $role): void
    {
        $validRoles = ['admin', 'member', 'owner', 'viewer'];
        
        if (!in_array($role, $validRoles)) {
            throw new \InvalidArgumentException("Invalid role: {$role}. Valid roles are: " . implode(', ', $validRoles));
        }
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

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    public function hasPermission(string $permission): bool
    {
        return match ($this->role) {
            'owner' => true,
            'admin' => in_array($permission, ['create', 'read', 'update', 'delete']),
            'member' => in_array($permission, ['create', 'read', 'update']),
            'viewer' => in_array($permission, ['read']),
            default => false,
        };
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'organization_id' => $this->organizationId,
        ];
    }
}
