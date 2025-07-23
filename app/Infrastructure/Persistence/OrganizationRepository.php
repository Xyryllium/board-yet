<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function __construct(private Organization $organization)
    {
    }

    public function save(array $data): Organization
    {
        return $this->organization->create($data);
    }
}
