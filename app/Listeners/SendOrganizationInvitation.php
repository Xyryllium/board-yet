<?php

namespace App\Listeners;

use Exception;
use App\Application\Organization\Services\OrganizationService;
use App\Events\OrganizationInvitationSent;
use App\Mail\OrganizationInvitationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrganizationInvitation implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private OrganizationService $organizationService
    ) {
    }

    public function handle(OrganizationInvitationSent $event): void
    {
        $invitation = $event->invitation;

        Log::channel('organization')->info("SendOrganizationInvitation listener started", [
            'invitation_id' => $invitation->id,
            'email' => $invitation->email,
            'listener_attempt' => $this->attempts(),
        ]);

        $cacheKey = "invitation_email_sent_{$invitation->id}";

        if (cache()->has($cacheKey)) {
            Log::channel('organization')->info("Email already sent for invitation", [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
            ]);
            return;
        }

        try {
            Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation, $this->organizationService));

            cache()->put($cacheKey, true, now()->addHours(24));

            Log::channel('organization')->info("Invitation email sent successfully", [
                'organization_id' => $invitation->organization_id,
                'email' => $invitation->email,
                'invitation_id' => $event->invitation->id ?? null,
            ]);
        } catch (Exception $e) {
            Log::channel('organization')->error("Failed to send invitation email", [
                'organization_id' => $invitation->organization_id,
                'email' => $invitation->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
