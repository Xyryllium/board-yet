<?php

namespace App\Domain\Organization\Services;

use Illuminate\Validation\ValidationException;

class OrganizationDomainService
{
    public function __construct(private readonly ValidationException $validationException)
    {
    }

    public function validateOrganizationName(string $name)
    {
        if (\strlen($name) < 3) {
            throw $this->validationException->withMessages([
                'name' => 'Organization name must be at least 3 characters.'
            ]);
        }
    }
}
