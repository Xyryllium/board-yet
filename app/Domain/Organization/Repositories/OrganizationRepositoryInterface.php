<?php

namespace App\Domain\Organization\Repositories;

use App\Models\Organization;

interface OrganizationRepositoryInterface
{
    public function save(array $data): Organization;
}
