<?php

namespace App\Application\Organization\Services;

use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Services\OrganizationDomainService;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public function __construct(
        private OrganizationDomainService $orgDomainService,
        private OrganizationRepositoryInterface $orgRepository,
        private Connection $database
    ) {
    }

    public function create(User $user, array $data): Organization
    {
        $this->orgDomainService->validateOrganizationName($data['name']);

        return $this->database->transaction(function () use ($user, $data) {
            $organization = $this->orgRepository->save([
                'name' => $data['name'],
                'owner_id' => $user->id
            ]);

            $organization->users()->attach($user->id, ['role' => 'admin']);

            $user->current_organization_id = $organization->id;
            $user->save();

            return $organization;
        });
    }
}
