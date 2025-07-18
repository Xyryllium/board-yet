<?php

namespace App\Domain\Organization\Services;

use Illuminate\Validation\ValidationException;

class OrganizationDomainService
{
    public function validateOrganizationName(string $name)
    {
        if(\strlen($name) < 3) {
            throw ValidationException::withMessages([
                'name' => 'Organization name must be at least 3 characters.'
            ]);
        }
    }
}