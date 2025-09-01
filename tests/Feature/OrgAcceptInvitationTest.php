<?php

use App\Domain\Organization\Enums\InvitationStatus;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a user can accept a valid invitation', function () {
    $user = User::factory()->create([
        'email' => 'john.doe@example.com'
    ]);

    $organization = Organization::factory()->create([
        'owner_id' => 1,
    ]);

    $invitation = OrganizationInvitation::factory()->create([
        'email' => $user->email,
        'token' => 'valid-token',
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
        'owner_id' => 1,
    ]);

    $invitation = OrganizationInvitation::factory()->create([
        'email' => $user->email,
        'token' => 'valid-token',
        'organization_id' => $organization->id,
    ]);

    $response = $this->actingAs($user)
                ->postJson('/api/organizations/invitations/accept', [
            'token' => 'invalid-token',
    ]);

    $response->assertStatus(400)
             ->assertJson([
                 'message' => 'Invalid invitation token',
                 'status' => 'error',
             ]);
});
