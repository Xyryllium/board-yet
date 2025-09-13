<?php

namespace App\Domain\User\Services;

use App\Domain\Auth\ValueObjects\UserRole;
use App\Domain\User\Repositories\UserRepositoryInterface;

class UserRoleDomainService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function getUserRoleInCurrentOrganization(int $userId): ?UserRole
    {
        $currentOrganizationId = $this->userRepository->getUserCurrentOrganizationId($userId);
        
        if (!$currentOrganizationId) {
            return null;
        }

        $role = $this->userRepository->getUserRoleInOrganization($userId, $currentOrganizationId);
        
        if (!$role) {
            return null;
        }

        return new UserRole(
            role: $role,
            organizationId: $currentOrganizationId
        );
    }

    public function getUserRoleInOrganization(int $userId, int $organizationId): ?UserRole
    {
        $role = $this->userRepository->getUserRoleInOrganization($userId, $organizationId);
        
        if (!$role) {
            return null;
        }

        return new UserRole(
            role: $role,
            organizationId: $organizationId
        );
    }

    public function getUserOrganizations(int $userId): array
    {
        return $this->userRepository->getUserOrganizations($userId);
    }

    public function hasRoleInOrganization(int $userId, int $organizationId, string $requiredRole): bool
    {
        $userRole = $this->getUserRoleInOrganization($userId, $organizationId);
        
        if (!$userRole) {
            return false;
        }

        return $userRole->role === $requiredRole;
    }

    public function canAccessOrganization(int $userId, int $organizationId): bool
    {
        return $this->getUserRoleInOrganization($userId, $organizationId) !== null;
    }
}
