<?php

namespace App\Domain\Organization\Services;

use Illuminate\Validation\ValidationException;

class OrganizationDomainService
{
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateOrganizationName(string $name)
    {
        if (\strlen($name) < 3) {
            throw ValidationException::withMessages([
                'name' => 'Organization name must be at least 3 characters.'
            ]);
        }
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validateSubdomain(string $subdomain)
    {
        if (\strlen($subdomain) < 3 || \strlen($subdomain) > 63) {
            throw ValidationException::withMessages([
                'subdomain' => 'Subdomain must be between 3 and 63 characters.'
            ]);
        }
        
        if (!preg_match('/^[a-z0-9]([a-z0-9-]{1,61}[a-z0-9])?$/', $subdomain)) {
            throw ValidationException::withMessages([
                'subdomain' => 'Subdomain must contain only lowercase letters, numbers, and hyphens. It must start and end with alphanumeric characters.'
            ]);
        }

        if (strpos($subdomain, '--') !== false) {
            throw ValidationException::withMessages([
                'subdomain' => 'Subdomain cannot contain consecutive hyphens.'
            ]);
        }

        $reservedSubdomains = [
            'www', 'api', 'admin', 'app', 'mail', 'ftp', 'blog', 'shop', 'store',
            'support', 'help', 'docs', 'status', 'dev', 'test', 'staging', 'prod'
        ];

        if (in_array(strtolower($subdomain), $reservedSubdomains)) {
            throw ValidationException::withMessages([
                'subdomain' => 'This subdomain is reserved and cannot be used.'
            ]);
        }
    }
}
