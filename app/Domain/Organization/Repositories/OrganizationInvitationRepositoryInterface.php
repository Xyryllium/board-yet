<?php

namespace App\Domain\Organization\Repositories;

use App\Domain\Organization\Entities\OrganizationInvitation;
use App\Models\OrganizationInvitation as ModelsOrganizationInvitation;

interface OrganizationInvitationRepositoryInterface
{
    public function create(OrganizationInvitation $data): ModelsOrganizationInvitation;
    public function findByToken(string $token): ?ModelsOrganizationInvitation;
    public function updateStatus(string $token, string $status): void;
}
