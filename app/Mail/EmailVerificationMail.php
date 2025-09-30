<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class EmailVerificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        private User $user
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email Address - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.email-verification',
            with: [
                'verificationUrl' => $this->verificationUrl(),
                'notifiable' => $this->user,
            ]
        );
    }

    protected function verificationUrl(): string
    {
        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $userId = $this->user->getKey();
        $hash = sha1($this->user->getEmailForVerification());

        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $userId,
                'hash' => $hash,
            ]
        );

        $parsedUrl = parse_url($signedUrl);
        $queryParams = $parsedUrl['query'] ?? '';

        return $frontendUrl . '/email/verify/' . $userId . '/' . $hash . '?' . $queryParams;
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
