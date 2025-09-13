<?php

namespace App\Http\Controllers;

use Exception;
use App\Application\Auth\Services\AuthService;
use App\Domain\Auth\Exceptions\InvalidCredentialsException;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
    {
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

            $authenticatedUser = $this->authService->login($credentials);

            return response()->json($authenticatedUser->toArray());
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
            return response()->json([
                'message' => 'An unexpected error occurred'
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->authService->logoutAllUserTokens($user->id);

            return response()->json(['message' => 'Logged out']);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Logout failed'
            ], 500);
        }
    }
}
