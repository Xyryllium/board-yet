<?php

use App\Application\Organization\Services\OrganizationService;
use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

describe('Invitation URL Generation', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $this->organizationService = app(OrganizationService::class);
    });

    it('generates subdomain-based URL when organization has subdomain', function () {
        $organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
            'owner_id' => $this->user->id,
        ]);

        $invitation = OrganizationInvitation::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'invited@example.com',
            'token' => 'test-token-123',
        ]);

        $invitation->load('organization');

        $url = $this->organizationService->generateInvitationUrl($invitation);

        expect($url)->toContain('test-org');
        expect($url)->toContain('test-token-123');
        expect($url)->toContain('/invitations/accept/');
        
        expect($url)->toStartWith('http://');
    });

    it('generates fallback URL when organization has no subdomain', function () {
        $organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => null,
            'owner_id' => $this->user->id,
        ]);

        $invitation = OrganizationInvitation::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'invited@example.com',
            'token' => 'test-token-123',
        ]);

        $invitation->load('organization');

        $url = $this->organizationService->generateInvitationUrl($invitation);

        $expectedUrl = config('app.frontend_url') . '/invitations/accept/test-token-123';

        expect($url)->toBe($expectedUrl);
    });

    it('uses correct protocol based on environment', function () {
        $organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
            'owner_id' => $this->user->id,
        ]);

        $invitation = OrganizationInvitation::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'invited@example.com',
            'token' => 'test-token-123',
        ]);

        $invitation->load('organization');

        $url = $this->organizationService->generateInvitationUrl($invitation);

        expect($url)->toStartWith('http://');
    });

    it('includes port in local environment', function () {
        if (config('app.env') !== 'local') {
            $this->markTestSkipped('This test only runs in local environment');
        }

        $organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
            'owner_id' => $this->user->id,
        ]);

        $invitation = OrganizationInvitation::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'invited@example.com',
            'token' => 'test-token-123',
        ]);

        $invitation->load('organization');

        $url = $this->organizationService->generateInvitationUrl($invitation);

        expect($url)->toContain(':8000');
    });

    it('sends email with correct invitation URL', function () {
        Mail::fake();

        $organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
            'owner_id' => $this->user->id,
        ]);

        $invitation = OrganizationInvitation::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'invited@example.com',
            'token' => 'test-token-123',
        ]);

        $invitation->load('organization');

        Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation, $this->organizationService));

        Mail::assertSent(OrganizationInvitationMail::class, function ($mail) use ($invitation) {
            $expectedUrl = $this->organizationService->generateInvitationUrl($invitation);
            
            return $mail->hasTo($invitation->email) && 
                   str_contains($mail->render(), $expectedUrl);
        });
    });
});
