<?php

namespace App\Models;

use App\Domain\Organization\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'organization_id',
        'role',
        'status'
    ];

    protected $casts = [
        'status' => InvitationStatus::class,
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
