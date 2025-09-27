<?php

namespace App\Domain\Organization\Repositories;

use App\Models\Organization;
use App\Domain\Organization\Entities\Organization as OrganizationEntity;

interface OrganizationRepositoryInterface
{
    public function save(array $data): Organization;
    public function findBySubdomain(string $subdomain): ?OrganizationEntity;
    public function findById(int $organizationId): ?OrganizationEntity;
    public function update(int $organizationId, array $data): Organization;
    public function isSubdomainAvailable(string $subdomain, ?int $excludeId = null): bool;
}
