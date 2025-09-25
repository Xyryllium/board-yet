<?php

namespace App\Domain\Auth\Services;

use Exception;
use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Exceptions\TokenCreationException;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\Auth\ValueObjects\Credentials;
use App\Domain\Auth\ValueObjects\Token;
use App\Domain\Auth\ValueObjects\UserRole;
use App\Domain\User\Entities\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\UserRoleDomainService;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use Illuminate\Contracts\Hashing\Hasher;

class AuthDomainService
{
    public function __construct(
        private AuthRepositoryInterface $authRepository,
        private UserRepositoryInterface $userRepository,
        private UserRoleDomainService $userRoleService,
        private OrganizationRepositoryInterface $orgRepository,
        private Hasher $hasher,
    ) {
    }

    public function authenticate(Credentials $credentials): AuthenticatedUser
    {
        if (!$credentials->isValid()) {
            throw new InvalidCredentialsException('Invalid credentials format');
        }

        $user = $this->authRepository->authenticate($credentials);

        if (!$user) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        if (!$this->hasher->check($credentials->password, $user->getPassword())) {
            throw new InvalidCredentialsException('Invalid email or password');
        }

        $role = $this->getUserRole($user);
        $token = $this->createTokenForUser($user, $role);
        $subdomain = $this->getOrganizationSubdomain($role?->organizationId);

        return new AuthenticatedUser(
            userId: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            token: $token,
            role: $role,
            subdomain: $subdomain,
        );
    }

    public function createUserWithToken(array $userData): AuthenticatedUser
    {
        $user = new User(
            name: $userData['name'],
            email: $userData['email'],
            password: $this->hasher->make($userData['password'])
        );

        $savedEloquentUser = $this->userRepository->save($user);

        $savedUser = new User(
            name: $savedEloquentUser->name,
            email: $savedEloquentUser->email,
            password: $savedEloquentUser->password,
            userId: $savedEloquentUser->id,
        );

        $role = $this->getUserRole($savedUser);
        $token = $this->createTokenForUser($savedUser, $role);
        $subdomain = $this->getOrganizationSubdomain($role?->organizationId);

        return new AuthenticatedUser(
            userId: $savedUser->getId(),
            name: $savedUser->getName(),
            email: $savedUser->getEmail(),
            token: $token,
            role: $role,
            subdomain: $subdomain,
        );
    }

    public function logout(string $token): bool
    {
        return $this->authRepository->revokeToken($token);
    }

    public function logoutAllUserTokens(int $userId): bool
    {
        return $this->authRepository->revokeAllUserTokens($userId);
    }

    public function getAuthenticatedUser(string $token): ?AuthenticatedUser
    {
        return $this->authRepository->findUserByToken($token);
    }

    private function getUserRole(User $user): ?UserRole
    {
        return $this->userRoleService->getUserRoleInCurrentOrganization($user->getId());
    }

    private function getOrganizationSubdomain(?int $organizationId): ?string
    {
        if (!$organizationId) {
            return null;
        }

        $organization = $this->orgRepository->findById($organizationId);
        return $organization?->getSubdomain();
    }

    private function createTokenForUser(User $user, ?UserRole $role = null): Token
    {
        try {
            return $this->authRepository->createToken(
                $user,
                $role?->role,
                $role?->organizationId
            );
        } catch (Exception $e) {
            throw new TokenCreationException('Failed to create authentication token: ' . $e->getMessage());
        }
    }
}
