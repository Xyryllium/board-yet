<?php

namespace App\Application\User\Services;

use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User as EloquentUser;
use Illuminate\Contracts\Hashing\Hasher;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected Hasher $hasher;

    public function __construct(UserRepositoryInterface $userRepository, Hasher $hasher)
    {
        $this->userRepository = $userRepository;
        $this->hasher = $hasher;
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
}
