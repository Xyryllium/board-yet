<?php

namespace App\Http\Controllers;

use Exception;
use App\Application\Auth\Services\AuthService;
use App\Application\PasswordReset\Services\PasswordResetService;
use App\Application\User\Services\UserService;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        private PasswordResetService $passwordResetService
    ) {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $authenticatedUser = $this->authService->register($data);

            return response()->json([
                'success' => true,
                'data' => $authenticatedUser->toArray(),
                'message' => 'User created successfully'
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred'
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
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
            $responseData = $sessionData->getResponseData();

            $response = response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => 'Login successful'
            ], 200);

            if (isset($responseData['token'])) {
                $response->cookie(
                    'board_yet_auth_token',
                    $responseData['token'],
                    config('session.lifetime'),
                    config('session.path'),
                    config('session.domain'),
                    true,
                    false,
                    false,
                    config('session.same_site')
                );
            }

            return $response;
        } catch (InvalidCredentialsException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function currentUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
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
                'success' => false,
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
                    $this->authService->logout($token->plainTextToken ?? '');
                }
            }

            $response = response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

            $response->cookie(
                'board_yet_auth_token',
                '',
                -1, // Expire immediately
                '/',
                config('session.domain'),
                true,
                false,
                false,
                'strict'
            );

            return $response;
        } catch (Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logout failed'
            ], 500);
        }
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $this->passwordResetService->requestPasswordReset($data['email']);

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been sent to your email address.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Forgot password error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'email' => 'required|email|exists:users,email',
                'token' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $this->passwordResetService->resetPassword(
                $data['email'],
                $data['token'],
                $data['password']
            );

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Reset password error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
