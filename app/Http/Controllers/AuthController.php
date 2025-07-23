<?php

namespace App\Http\Controllers;

use App\Application\User\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthController extends Controller
{
    public function __construct(private UserService $userService, private AuthFactory $auth)
    {
    }

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => [
                        'required',
                        'string',
                        'min:8',
                        // Minimum length
                        'regex:/[a-z]/',
                        // At least one lowercase letter
                        'regex:/[A-Z]/',
                        // At least one uppercase letter
                        'regex:/[0-9]/',
                        // At least one number
                        'regex:/[@$!%*#?&]/',
                        // At least one special character
                    ],
        ]);

        $user = $this->userService->create($data);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->auth->guard()->attempt($credentials)) {
            return response()->json(['Invalid credentials'], 401);
        }

        $user = $this->auth->guard()->user();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
