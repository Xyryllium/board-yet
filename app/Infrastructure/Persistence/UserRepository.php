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
}