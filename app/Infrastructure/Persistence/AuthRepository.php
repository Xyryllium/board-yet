<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\Auth\ValueObjects\Credentials;
use App\Domain\Auth\ValueObjects\Token;
use App\Domain\Auth\ValueObjects\UserRole;
use App\Domain\User\Entities\User;
use App\Models\PersonalAccessToken;
use App\Models\User as EloquentUser;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Hashing\Hasher;

class AuthRepository implements AuthRepositoryInterface
{
    public function __construct(
        private AuthFactory $authFactory,
        private Hasher $hasher,
    ) {
    }

    public function authenticate(Credentials $credentials): ?User
    {
        $authGuard = $this->authFactory->guard();
        
        if (!$authGuard->attempt($credentials->toArray())) {
            return null;
        }

        $eloquentUser = $authGuard->user();
        
        return new User(
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            password: $eloquentUser->password,
            userId: $eloquentUser->id,
        );
    }

    public function createToken(User $user, ?string $role = null, ?int $organizationId = null): Token
    {
        $eloquentUser = EloquentUser::find($user->getId());
        
        if (!$eloquentUser) {
            throw new \RuntimeException('User not found');
        }

        $token = $eloquentUser->createToken('api-token');
        
        PersonalAccessToken::where('id', $token->accessToken->id)->update([
            'role' => $role,
            'organization_id' => $organizationId,
        ]);

        return new Token(
            plainTextToken: $token->plainTextToken,
            role: $role,
            organizationId: $organizationId,
            userId: $user->getId(),
        );
    }

    public function revokeToken(string $token): bool
    {
        $personalAccessToken = PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken) {
            return false;
        }

        return $personalAccessToken->delete();
    }

    public function revokeAllUserTokens(int $userId): bool
    {
        $eloquentUser = EloquentUser::find($userId);
        
        if (!$eloquentUser) {
            return false;
        }

        return $eloquentUser->tokens()->delete() > 0;
    }

    public function findUserByToken(string $token): ?AuthenticatedUser
    {
        $personalAccessToken = PersonalAccessToken::findToken($token);
        
        if (!$personalAccessToken) {
            return null;
        }

        $eloquentUser = $personalAccessToken->tokenable;
        
        if (!$eloquentUser) {
            return null;
        }

        $userRole = null;
        if ($personalAccessToken->role && $personalAccessToken->organization_id) {
            $userRole = new UserRole(
                role: $personalAccessToken->role,
                organizationId: $personalAccessToken->organization_id,
            );
        }

        return new AuthenticatedUser(
            id: $eloquentUser->id,
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            token: new Token(
                plainTextToken: $personalAccessToken->plainTextToken ?? '',
                role: $personalAccessToken->role,
                organizationId: $personalAccessToken->organization_id,
                userId: $eloquentUser->id,
            ),
            role: $userRole,
        );
    }
}
