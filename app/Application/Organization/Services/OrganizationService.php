<?php

namespace App\Application\Organization\Services;

use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Services\OrganizationDomainService;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public function __construct(private OrganizationDomainService $orgDomainService, private OrganizationRepositoryInterface $organizationRepository)
    {
        
    }
    public function create(User $user, array $data): Organization
    {
        $this->orgDomainService->validateOrganizationName($data['name']);

        return DB::transaction(function () use ($user, $data) {
            $organization = $this->organizationRepository->save([
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