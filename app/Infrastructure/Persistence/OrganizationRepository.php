<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Models\Organization;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function save(array $data): Organization
    {
        return Organization::create($data);
    }
}