<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Organization\Entities\OrganizationInvitation as EntitiesOrganizationInvitation;
use App\Domain\Organization\Repositories\OrganizationInvitationRepositoryInterface;
use App\Models\OrganizationInvitation;

class OrganizationInvitationRepository implements OrganizationInvitationRepositoryInterface
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
}
