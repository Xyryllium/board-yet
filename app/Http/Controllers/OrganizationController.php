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

    public function updateSettings(Request $request, int $organizationId): JsonResponse
    {
        $data = $request->validate($this->getUpdateSettingsValidationRules($organizationId));

        try {
            $organization = $this->organizationService->updateSettings($organizationId, $data);

            return response()->json([
                'message' => 'Organization settings updated successfully!',
                'organization' => $organization
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating organization settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateSubdomain(Request $request): JsonResponse
    {
        $request->validate([
            'subdomain' => 'required|string|min:3|max:63',
            'exclude' => 'nullable|integer|exists:organizations,id'
        ]);

        $subdomain = $request->input('subdomain');
        $excludeId = $request->input('exclude');

        try {
            $this->organizationService->validateSubdomainFormat($subdomain);

            $isAvailable = $this->organizationService->isSubdomainAvailable($subdomain, $excludeId);

            return response()->json([
                'success' => true,
                'available' => $isAvailable,
                'error' => $isAvailable ? null : 'Subdomain is already taken'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = $e->validator->errors()->first('subdomain');

            if (
                str_contains($errorMessage, 'format')
                || str_contains($errorMessage, 'regex')
                || str_contains($errorMessage, 'lowercase')
                || str_contains($errorMessage, 'alphanumeric')
            ) {
                $errorMessage = 'Invalid format';
            } elseif (str_contains($errorMessage, 'reserved')) {
                $errorMessage = 'Reserved';
            }

            return response()->json([
                'success' => true,
                'available' => false,
                'error' => $errorMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'available' => false,
                'error' => 'Error validating subdomain'
            ], 500);
        }
    }

    private function getUpdateSettingsValidationRules(int $organizationId): array
    {
        return [
            'subdomain' => [
                'nullable',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9-]{1,61}[a-z0-9])?$/',
                "unique:organizations,subdomain,{$organizationId}"
            ],
            'settings' => ['required', 'array'],
            'settings.theme' => ['nullable', 'array'],
            'settings.theme.primaryColor' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'settings.branding' => ['nullable', 'array'],
            'settings.branding.companyName' => ['nullable', 'string', 'max:255'],
            'settings.branding.supportEmail' => ['nullable', 'email', 'max:255'],
            'settings.*' => ['nullable'], // Allow any additional settings fields
        ];
    }
}
