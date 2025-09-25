<?php

namespace App\Http\Controllers;

use Exception;
use App\Application\Auth\Services\AuthService;
use App\Application\User\Services\UserService;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $authenticatedUser = $this->authService->register($data);

            return response()->json([
                'message' => 'User created successfully',
                ...$authenticatedUser->toArray(),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database error occurred'
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $sessionData = $this->authService->loginWithSession($credentials, 7);

            $response = response()->json($sessionData->getResponseData());

            $cookieData = $sessionData->getCookieData();

            $response->withCookie(cookie(
                name: $cookieData['name'],
                value: $cookieData['value'],
                minutes: $cookieData['minutes'],
                path: $cookieData['path'],
                domain: $cookieData['domain'],
                secure: $cookieData['secure'],
                httpOnly: $cookieData['httpOnly'],
                sameSite: $cookieData['sameSite']
            ));

            $response->header('Authorization', 'Bearer ' . $sessionData->token->plainTextToken);

            return $response;
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function currentUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userData = $this->userService->getCurrentUser($user);

            return response()->json($userData);
        } catch (Exception $e) {
            Log::error('Get user data error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user) {
                $token = $user->currentAccessToken();
                if ($token) {
                    // Use the DDD approach for logout
                    $this->authService->logout($token->plainTextToken ?? '');
                }
            }

            $response = response()->json(['message' => 'Logged out successfully']);

            // Clear the API token cookie
            $response->withCookie(cookie(
                name: 'api_token',
                value: '',
                minutes: -1,
                path: '/',
                domain: config('app.cookie_domain', 'api-test-board.com'),
                secure: config('app.secure_cookies', false),
                httpOnly: true,
                sameSite: 'lax'
            ));

            return $response;
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Logout failed'
            ], 500);
        }
    }
}
