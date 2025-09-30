<?php

namespace App\Application\User\Services;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\UserRoleDomainService;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Models\User as EloquentUser;
use Illuminate\Contracts\Hashing\Hasher;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected Hasher $hasher,
        protected UserRoleDomainService $userRoleService,
        protected OrganizationRepositoryInterface $orgRepository
    ) {
    }

    public function create(array $userData): EloquentUser
    {
        $user = new User(
            name: $userData['name'],
            email: $userData['email'],
            password: $this->hasher->make($userData['password'])
        );

        return $this->userRepository->save($user);
    }

    public function getUsersByOrganizationId(User $user, int $organizationId): array
    {
        return $this->userRepository->getUsersByOrganizationId($user, $organizationId);
    }

    public function getCurrentUser(EloquentUser $eloquentUser): array
    {
        $userRole = $this->userRoleService->getUserRoleInCurrentOrganization($eloquentUser->id);

        $currentOrganization = null;
        if ($eloquentUser->current_organization_id) {
            $currentOrganization = $this->orgRepository->findById($eloquentUser->current_organization_id);
        }

        return [
            'id' => $eloquentUser->id,
            'name' => $eloquentUser->name,
            'email' => $eloquentUser->email,
            'role' => $userRole?->role,
            'organization_id' => $currentOrganization?->organizationId,
            'subdomain' => $currentOrganization?->subdomain,
            'email_verified' => $eloquentUser->email_verified_at !== null,
        ];
    }
}
