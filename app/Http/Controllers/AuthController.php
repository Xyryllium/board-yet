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

            Log::channel('auth')->info("User registration attempt", [
                'email' => $data['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $authenticatedUser = $this->authService->register($data);

            Log::channel('auth')->info("User registration successful", [
                'user_id' => $authenticatedUser->userId,
                'email' => $data['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $authenticatedUser->toArray(),
                'message' => 'User created successfully. Please check your email to verify your account.'
            ], 201);
        } catch (ValidationException $e) {
            Log::channel('auth')->warning("User registration validation failed", [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (QueryException $e) {
            Log::channel('auth')->error("User registration database error", [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Database error occurred'
            ], 500);
        } catch (Exception $e) {
            Log::channel('auth')->error("User registration unexpected error", [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'exception' => $e,
            ]);

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

            Log::channel('auth')->info("User login attempt", [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $sessionData = $this->authService->loginWithSession($credentials, 7);
            $responseData = $sessionData->getResponseData();

            Log::channel('auth')->info("User login successful", [
                'user_id' => $responseData['user']['id'] ?? null,
                'email' => $credentials['email'],
                'ip' => $request->ip(),
            ]);

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
            Log::channel('auth')->warning("User login failed - invalid credentials", [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        } catch (ValidationException $e) {
            Log::channel('auth')->warning("User login validation failed", [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::channel('auth')->error('Login error: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
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
                Log::channel('auth')->warning("Unauthenticated user data request", [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $userData = $this->userService->getCurrentUser($user);

            Log::channel('auth')->debug("User data requested", [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            return response()->json($userData);
        } catch (Exception $e) {
            Log::channel('auth')->error('Get user data error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
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
                Log::channel('auth')->info("User logout", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip' => $request->ip(),
                ]);

                $token = $user->currentAccessToken();
                if ($token) {
                    $this->authService->logout($token->plainTextToken ?? '');
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ], 200)->cookie(
                    'api_token',
                    '',
                    -1,
                    '/',
                    null,
                    true,
                    true,
                    false,
                    'None'
                );
            }

            Log::channel('auth')->warning("Logout attempt by unauthenticated user", [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200)->cookie(
                'board_yet_auth_token',
                '',
                -1,
                '/',
                config('session.domain'),
                true,
                false,
                false,
                'strict'
            );
        } catch (Exception $e) {
            Log::channel('auth')->error('Logout error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
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

            Log::channel('auth')->info("Password reset requested", [
                'email' => $data['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $this->passwordResetService->requestPasswordReset($data['email']);

            Log::channel('auth')->info("Password reset email sent", [
                'email' => $data['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been sent to your email address.'
            ], 200);
        } catch (ValidationException $e) {
            Log::channel('auth')->warning("Password reset validation failed", [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::channel('auth')->error('Forgot password error: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
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

            Log::channel('auth')->info("Password reset attempt", [
                'email' => $data['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $this->passwordResetService->resetPassword(
                $data['email'],
                $data['token'],
                $data['password']
            );

            Log::channel('auth')->info("Password reset successful", [
                'email' => $data['email'],
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password has been reset successfully'
            ], 200);
        } catch (ValidationException $e) {
            Log::channel('auth')->warning("Password reset validation failed", [
                'email' => $request->input('email'),
                'errors' => $e->errors(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::channel('auth')->error('Reset password error: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'ip' => $request->ip(),
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
