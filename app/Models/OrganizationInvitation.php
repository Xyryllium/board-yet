<?php

namespace App\Models;

use App\Domain\Organization\Enums\InvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
