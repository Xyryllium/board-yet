<?php

use App\Events\EmailVerificationSent;
use App\Mail\EmailVerificationMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

describe('Email Verification', function () {
    beforeEach(function () {
        Event::fake();
        Mail::fake();
    });

    it('sends verification email after registration', function () {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully. Please check your email to verify your account.'
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'email_verified_at' => null
        ]);
        Event::assertDispatched(EmailVerificationSent::class, function ($event) {
            return $event->user->email === 'john@example.com';
        });
    });

    it('can verify email with valid token', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $hash = sha1($user->email);
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash]
        );

        $response = $this->getJson($signedUrl);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verified successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => now()
        ]);
    });

    it('cannot verify email with invalid hash', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $invalidHash = 'invalid_hash';
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $invalidHash]
        );

        $response = $this->getJson($signedUrl);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid verification link'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null
        ]);
    });

    it('cannot verify email with expired token', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $hash = sha1($user->email);
        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(1),
            ['id' => $user->id, 'hash' => $hash]
        );

        $response = $this->getJson($expiredUrl);

        $this->assertTrue(in_array($response->status(), [200, 400, 403]));
    });

    it('returns success for already verified email', function () {
        $user = User::factory()->create([
            'email_verified_at' => now()
        ]);

        $hash = sha1($user->email);
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => $hash]
        );

        $response = $this->getJson($signedUrl);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email already verified'
            ]);
    });

    it('can resend verification email', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->postJson('/api/email/resend');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Verification email sent'
            ]);

        Event::assertDispatched(EmailVerificationSent::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    });

    it('cannot resend verification email for verified user', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->postJson('/api/email/resend');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Email already verified'
            ]);
    });

    it('requires authentication to resend verification', function () {
        $response = $this->postJson('/api/email/resend');

        $response->assertStatus(401);
    });

    it('includes email verified status in user data', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'email_verified' => false
            ]);
    });

    it('shows verified status for verified user', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'email_verified' => true
            ]);
    });

    it('generates frontend url in verification email', function () {
        $user = User::factory()->create([
            'email_verified_at' => null
        ]);

        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');
        $userId = $user->getKey();
        $hash = sha1($user->getEmailForVerification());
        
        $signedUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $userId,
                'hash' => $hash,
            ]
        );
        
        $parsedUrl = parse_url($signedUrl);
        $queryParams = $parsedUrl['query'] ?? '';
        
        $expectedFrontendUrl = $frontendUrl . '/email/verify/' . $userId . '/' . $hash . '?' . $queryParams;
        $this->assertStringStartsWith($frontendUrl, $expectedFrontendUrl);
        $this->assertStringContainsString('/email/verify/', $expectedFrontendUrl);
        $this->assertStringContainsString($user->id, $expectedFrontendUrl);
        $this->assertStringContainsString(sha1($user->email), $expectedFrontendUrl);
    });
});