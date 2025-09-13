<?php

namespace App\Infrastructure\Persistence;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User as EloquentUser;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private EloquentUser $user)
    {
    }

    public function save(User $user): EloquentUser
    {
        return $this->user->create([
            'name' => $user->getName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
        ]);
    }

    public function findByEmail(string $email): ?EloquentUser
    {
        return $this->user->where('email', $email)->first();
    }

    public function findById(int $userId): ?EloquentUser
    {
        return $this->user->find($userId);
    }

    public function getUserRoleInOrganization(int $userId, int $organizationId): ?string
    {
        $user = $this->user->find($userId);
        if (!$user) {
            return null;
        }

        $pivot = $user->organizations()
            ->where('organization_id', $organizationId)
            ->first()?->pivot;

        return $pivot?->role;
    }

    public function getUserCurrentOrganizationId(int $userId): ?int
    {
        $user = $this->user->find($userId);
        return $user?->current_organization_id;
    }

    public function getUserOrganizations(int $userId): array
    {
        $user = $this->user->find($userId);
        if (!$user) {
            return [];
        }

        return $user->organizations()
            ->withPivot('role')
            ->get()
            ->map(function ($org) {
                return [
                    'id' => $org->id,
                    'name' => $org->name,
                    'role' => $org->pivot->role,
                ];
            })
            ->toArray();
    }
}
