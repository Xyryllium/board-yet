<?php

namespace App\Http\Controllers;

use App\Application\Organization\Services\OrganizationService;
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
        $data = $request->validate([
            'token' => 'required|string',
        ]);

        $this->service->acceptInvitation(
            $data['token'],
            auth()->user()
        );

        return response()->json([
            'message' => 'Invitation accepted successfully!'
        ]);
    }
}
