<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use App\Models\User as EloquentUser;

interface UserRepositoryInterface
{
    public function save(User $user): EloquentUser;
    public function findByEmail(string $email): ?EloquentUser;
    public function findById(int $userId): ?EloquentUser;
    public function getUserRoleInOrganization(int $userId, int $organizationId): ?string;
    public function getUserCurrentOrganizationId(int $userId): ?int;
    public function getUserOrganizations(int $userId): array;
}
