<?php

use App\Domain\Organization\Enums\InvitationStatus;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('a user can accept a valid invitation', function () {
    $user = User::factory()->create([
        'email' => 'john.doe@example.com'
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $invitation = OrganizationInvitation::factory()->create([
        'email' => $user->email,
        'token' => (string) Str::uuid(),
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)
                ->postJson('/api/organizations/invitations/accept', [
            'token' => $invitation->token,
    ]);

    $response->assertStatus(200)
             ->assertJson([
                 'message' => 'Invitation accepted successfully!',
                 'status' => 'invitation_accepted',
             ]);

    $this->assertDatabaseHas('organization_invitations', [
        'email' => $user->email,
        'token' => $invitation->token,
        'role' => 'member',
        'status' => InvitationStatus::ACCEPTED->value,
    ]);

    $this->assertTrue(
        $user->organizations()->where('organization_id', $invitation->organization_id)->exists()
    );
});

test('a user cannot accept an invitation with an invalid token', function () {
    $user = User::factory()->create([
        'email' => 'john.doe@example.com'
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $user->id,
    ]);

    $invitation = OrganizationInvitation::factory()->create([
        'email' => $user->email,
        'token' => (string) Str::uuid(),
        'organization_id' => $organization->id,
    ]);

    $invalidToken = (string) Str::uuid();

    $response = $this->actingAs($user)
                ->postJson('/api/organizations/invitations/accept', [
            'token' => $invalidToken,
    ]);

    $response->assertStatus(400)
             ->assertJson([
                 'message' => 'Invalid invitation token',
                 'status' => 'error',
             ]);
});

test('a unregistered user can accept invitation and has not registered status ', function () {
    $orgOwner = User::factory()->create([
        'email' => 'john.doe@example.com'
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => $orgOwner->id,
    ]);

    $invitation = OrganizationInvitation::factory()->create([
        'email' => 'unregistered.user@example.com',
        'token' => (string) Str::uuid(),
        'organization_id' => $organization->id,
    ]);

    $response = $this->postJson('/api/organizations/invitations/accept', [
            'token' => $invitation->token,
    ]);

    $response->assertStatus(400)
             ->assertJson([
                 'message' => 'User is not registered.',
                 'status' => 'user_not_registered',
             ]);

    $this->assertDatabaseHas('organization_invitations', [
        'email' => 'unregistered.user@example.com',
        'token' => $invitation->token,
        'role' => 'member',
        'status' => InvitationStatus::PENDING->value,
    ]);
});
