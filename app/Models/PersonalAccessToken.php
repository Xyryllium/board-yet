<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'tokenable_type',
        'tokenable_id',
        'role',
        'organization_id',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
    ];

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;
    }

    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? null;
    }

    public function setOrganizationIdAttribute($value)
    {
        $this->attributes['organization_id'] = $value;
    }

    public function getOrganizationIdAttribute()
    {
        return $this->attributes['organization_id'] ?? null;
    }
}
