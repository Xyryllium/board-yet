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
            'name' => 'required|string|min:3|max:255'
        ]);

        $organization = $this->organizationService->create(auth()->user(), $data);

        return response()->json([
            'message' => 'Organization created succesfully!',
            'organization' => $organization
        ]);
    }
}
