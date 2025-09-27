<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Organization\Entities\Organization as EntitiesOrganization;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;
use Exception;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(private Organization $organization)
    {
    }

    public function save(array $data): Organization
    {
        return $this->organization->create($data);
    }

    public function findBySubdomain(string $subdomain): ?EntitiesOrganization
    {
        $organization = $this->organization->where('subdomain', $subdomain)->first();

        if (!$organization) {
            return null;
        }

        return $this->toDomain($organization);
    }

    public function findById(int $orgId): ?EntitiesOrganization
    {
        $organization = $this->organization->find($orgId);

        if (!$organization) {
            return null;
        }

        return $this->toDomain($organization);
    }

    public function update(int $organizationId, array $data): Organization
    {
        $organization = $this->organization->find($organizationId);

        if (!$organization) {
            throw new Exception("Organization with ID {$organizationId} not found");
        }

        $organization->update($data);

        return $organization->fresh();
    }

    public function isSubdomainAvailable(string $subdomain, ?int $excludeId = null): bool
    {
        $query = $this->organization->where('subdomain', $subdomain);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    private function toDomain(Organization $organization): EntitiesOrganization
    {
        return EntitiesOrganization::fromArray([
            'id' => $organization->id,
            'name' => $organization->name,
            'subdomain' => $organization->subdomain,
            'settings' => $organization->settings,
            'created_at' => $organization->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $organization->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
