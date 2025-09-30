<?php

namespace App\Listeners;

use Exception;
use App\Events\EmailVerificationSent;
use App\Mail\EmailVerificationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailVerification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EmailVerificationSent $event): void
    {
        $user = $event->user;

        Log::channel('auth')->info("SendEmailVerification listener started", [
            'user_id' => $user->id,
            'email' => $user->email,
            'listener_attempt' => $this->attempts(),
        ]);

        $cacheKey = "email_verification_sent_{$user->id}";

        if (cache()->has($cacheKey)) {
            Log::channel('auth')->info("Email verification already sent for user", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        try {
            Mail::to($user->email)->send(new EmailVerificationMail($user));

            cache()->put($cacheKey, true, now()->addHours(24));

            Log::channel('auth')->info("Email verification sent successfully", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (Exception $e) {
            Log::channel('auth')->error("Failed to send email verification", [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'exception' => $e,
            ]);

            throw $e;
        }
    }
}
