<?php

namespace App\Application\Auth\Services;

use Exception;
use RuntimeException;
use App\Domain\Auth\Entities\AuthenticatedUser;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Domain\Auth\Services\AuthDomainService;
use App\Domain\Auth\ValueObjects\Credentials;
use App\Domain\Auth\ValueObjects\Token;
use App\Domain\Auth\ValueObjects\UserRole;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private AuthDomainService $authDomainService,
        private AuthFactory $authFactory,
    ) {
    }

    public function login(array $credentials): AuthenticatedUser
    {
        try {
            $credentialsVO = new Credentials(
                email: $credentials['email'],
                password: $credentials['password']
            );

            return $this->authDomainService->authenticate($credentialsVO);
        } catch (InvalidCredentialsException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new RuntimeException('Login failed: ' . $e->getMessage());
        }
    }

    public function register(array $userData): AuthenticatedUser
    {
        try {
            unset($userData['confirmPassword']);

            return $this->authDomainService->createUserWithToken($userData);
        } catch (ValidationException $e) {
            throw $e;
        } catch (QueryException $e) {
            throw new RuntimeException('Registration failed due to database error');
        } catch (Exception $e) {
            throw new RuntimeException('Registration failed: ' . $e->getMessage());
        }
    }

    public function logout(string $token): bool
    {
        try {
            return $this->authDomainService->logout($token);
        } catch (Exception $e) {
            throw new RuntimeException('Logout failed: ' . $e->getMessage());
        }
    }

    public function logoutAllUserTokens(int $userId): bool
    {
        try {
            return $this->authDomainService->logoutAllUserTokens($userId);
        } catch (Exception $e) {
            throw new RuntimeException('Logout all tokens failed: ' . $e->getMessage());
        }
    }

    public function getCurrentUser(string $token): ?AuthenticatedUser
    {
        try {
            return $this->authDomainService->getAuthenticatedUser($token);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to get current user: ' . $e->getMessage());
        }
    }

    public function getCurrentUserFromRequest($request): ?AuthenticatedUser
    {
        $user = $request->user();
        if (!$user) {
            return null;
        }

        $token = $user->currentAccessToken();
        if (!$token) {
            return null;
        }

        return new AuthenticatedUser(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            token: new Token(
                plainTextToken: $token->plainTextToken ?? '',
                role: $token->role,
                organizationId: $token->organization_id,
                userId: $user->id,
            ),
            role: $token->role ? new UserRole(
                role: $token->role,
                organizationId: $token->organization_id ?? 0,
            ) : null,
        );
    }
}
