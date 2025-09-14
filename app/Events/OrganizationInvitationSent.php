<?php

namespace App\Events;

use App\Models\OrganizationInvitation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public OrganizationInvitation $invitation
    ) {
    }
}
