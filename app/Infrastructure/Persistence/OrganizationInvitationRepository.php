<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Organization\Entities\OrganizationInvitation as EntitiesOrganizationInvitation;
use App\Domain\Organization\Repositories\OrgInvitationRepositoryInterface;
use App\Models\OrganizationInvitation;

class OrganizationInvitationRepository implements OrgInvitationRepositoryInterface
{
    public function create(EntitiesOrganizationInvitation $orgInviteEntity): OrganizationInvitation
    {
        return OrganizationInvitation::create([
            'email' => $orgInviteEntity->getEmail(),
            'token' => $orgInviteEntity->getToken(),
            'organization_id' => $orgInviteEntity->getOrganizationId(),
            'role' => $orgInviteEntity->getRole(),
            'status' => $orgInviteEntity->getStatus(),
        ]);
    }

    public function findByToken(string $token): ?OrganizationInvitation
    {
        return OrganizationInvitation::where('token', $token)->first();
    }

    public function updateStatus(string $token, string $status): void
    {
        OrganizationInvitation::where('token', $token)
            ->update(['status' => $status]);
    }

    public function findOrgDetailsByToken(string $token): array
    {
        $invitation = OrganizationInvitation::with('organization.owner')
            ->where('token', $token)
            ->first();

        if (!$invitation) {
            return [];
        }

        return [
            'organization' => [
                'id' => $invitation->organization->id,
                'name' => $invitation->organization->name,
                'owner' => [
                    'id' => $invitation->organization->owner->id,
                    'name' => $invitation->organization->owner->name,
                    'email' => $invitation->organization->owner->email,
                ],
            ],
        ];
    }
}
