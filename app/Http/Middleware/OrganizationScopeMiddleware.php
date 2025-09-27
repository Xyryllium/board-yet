<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use RuntimeException;

class OrganizationScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'User not authenticated.'
            ], 401);
        }

        if (!$user->current_organization_id) {
            return response()->json([
                'message' => 'User does not belong to any organization.',
                'error' => 'NO_ORGANIZATION'
            ], 403);
        }

        if (!$user->relationLoaded('currentOrganization')) {
            $user->load('currentOrganization');
        }

        if (!$user->currentOrganization) {
            return response()->json([
                'message' => 'Current organization not found.',
                'error' => 'ORGANIZATION_NOT_FOUND'
            ], 403);
        }

        $isMember = $user->organizations()
            ->where('organization_id', $user->current_organization_id)
            ->exists();

        if (!$isMember) {
            return response()->json([
                'message' => 'User is not a member of the current organization.',
                'error' => 'NOT_ORGANIZATION_MEMBER'
            ], 403);
        }

        $request->attributes->set('organization_id', $user->current_organization_id);
        $request->attributes->set('organization', $user->currentOrganization);
        $request->attributes->set('user_role', $this->getUserRoleInOrganization($user, $user->current_organization_id));

        return $next($request);
    }

    private function getUserRoleInOrganization($user, int $organizationId): ?string
    {
        $membership = $user->organizations()
            ->where('organization_id', $organizationId)
            ->first();

        return $membership?->pivot?->role;
    }
}
