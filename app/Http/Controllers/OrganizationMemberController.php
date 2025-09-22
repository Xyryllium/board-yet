<?php

namespace App\Http\Controllers;

use Exception;
use App\Application\Organization\Services\OrganizationService;
use App\Domain\User\Exceptions\UserNotRegisteredException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function __construct(private OrganizationService $service)
    {
    }

    public function invite(Request $request, int $organizationId): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'role' => 'string|in:member,admin'
        ]);

        $invitation = $this->service->createInvitation(
            $organizationId,
            $data['email'],
            $data['role'] ?? 'member'
        );

        return response()->json([
            'message' => 'Invitation sent successfully!',
            'invitation' => $invitation
        ]);
    }

    public function acceptInvitation(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'token' => 'required|string',
            ]);

            $this->service->acceptInvitation(
                $data['token'],
                auth('sanctum')->user()
            );

            return response()->json([
            'message' => 'Invitation accepted successfully!',
            'status' => 'invitation_accepted',
            ]);
        } catch (UserNotRegisteredException $userException) {
            return response()->json([
                'message' => $userException->getMessage(),
                'status' => 'user_not_registered',
                'email' => $userException->email,
                'token' => $userException->token,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error',
            ], 400);
        }
    }

    public function listOrgDetails(string $token): JsonResponse
    {
        try {
            $organization = $this->service->listOrgDetails($token);

            return response()->json([
                'data' => $organization
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => 'error'
            ], 400);
        }
    }
}
