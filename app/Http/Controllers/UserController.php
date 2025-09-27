<?php

namespace App\Http\Controllers;

use App\Application\User\Services\UserService;
use App\Domain\User\Entities\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

    public function show(Request $request, int $organizationId): JsonResponse
    {
        $eloquentUser = $request->user();

        if (!$eloquentUser) {
            return response()->json([
                'message' => 'User not authenticated'
            ], 401);
        }

        $user = new User(
            name: $eloquentUser->name,
            email: $eloquentUser->email,
            password: $eloquentUser->password,
            userId: $eloquentUser->id
        );

        $users = $this->userService->getUsersByOrganizationId($user, $organizationId);
        return response()->json([
            'data' => $users,
        ], 200);
    }
}
