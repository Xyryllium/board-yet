<?php

namespace App\Http\Controllers;

use App\Application\Organization\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function __construct(private OrganizationService $organizationService)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'subdomain' => 'nullable|string|min:3|max:63
                            |regex:/^[a-z0-9]([a-z0-9-]{1,61}[a-z0-9])?$/
                            |unique:organizations,subdomain',
            'settings' => 'nullable|array'
        ]);

        /** @var \App\Models\User $user */
        $user = auth('sanctum')->user();

        $organization = $this->organizationService->create($user, $data);

        return response()->json([
            'message' => 'Organization created successfully!',
            'organization' => $organization
        ], 201);
    }

    public function listOrgDetailsBySubdomain(string $subdomain): JsonResponse
    {
        try {
            $organization = $this->organizationService->findBySubdomain($subdomain);

            if (!$organization) {
                return response()->json([
                    'message' => 'Organization not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'data' => $organization->toArray()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching organization details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
