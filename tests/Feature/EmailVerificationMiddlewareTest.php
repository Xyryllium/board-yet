<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Email Verification Middleware', function () {
    it('allows unverified user to view boards', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->getJson('/api/boards');

        $response->assertStatus(200);
    });

    it('allows unverified user to view specific board', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $board = \App\Models\Board::factory()->create([
            'organization_id' => $organization->id
        ]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->getJson("/api/boards/{$board->id}");

        $response->assertStatus(200);
    });

    it('allows unverified user to view columns', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $board = \App\Models\Board::factory()->create([
            'organization_id' => $organization->id
        ]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->getJson("/api/boards/{$board->id}/columns");

        $response->assertStatus(200);
    });

    it('allows unverified user to view cards', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $board = \App\Models\Board::factory()->create([
            'organization_id' => $organization->id
        ]);
        $column = \App\Models\BoardColumn::factory()->create([
            'board_id' => $board->id
        ]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->getJson("/api/columns/{$column->id}/cards");

        $response->assertStatus(200);
    });

    it('allows unverified user to view organization members', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->getJson("/api/users/{$organization->id}/members");

        $response->assertStatus(200);
    });

    it('prevents unverified user from creating organization', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->postJson('/api/organizations', [
                'name' => 'Test Organization'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('prevents unverified user from creating board', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson('/api/boards', [
                'name' => 'Test Board'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('prevents unverified user from updating board', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->putJson('/api/boards/1', [
                'name' => 'Updated Board'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('prevents unverified user from creating column', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson('/api/columns', [
                'name' => 'Test Column',
                'board_id' => 1
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('prevents unverified user from creating card', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson('/api/columns/1/cards', [
                'title' => 'Test Card'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('prevents unverified user from inviting users', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => null
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create();
        $user->organizations()->attach($organization->id, ['role' => 'admin']);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson("/api/organizations/{$organization->id}/invite", [
                'email' => 'newuser@example.com'
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your email address is not verified.',
                'error_code' => 'EMAIL_NOT_VERIFIED'
            ]);
    });

    it('allows verified user to create organization', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $response = $this->withApiToken($token)
            ->postJson('/api/organizations', [
                'name' => 'Test Organization'
            ]);

        $response->assertStatus(201);
    });

    it('allows verified user to create board', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson('/api/boards', [
                'name' => 'Test Board'
            ]);

        $response->assertStatus(201);
    });

    it('allows verified user to create column', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $board = \App\Models\Board::factory()->create([
            'organization_id' => $organization->id
        ]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson('/api/columns', [
                'name' => 'Test Column',
                'boardId' => $board->id
            ]);

        $response->assertStatus(201);
    });

    it('allows verified user to create card', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $board = \App\Models\Board::factory()->create([
            'organization_id' => $organization->id
        ]);
        $column = \App\Models\BoardColumn::factory()->create([
            'board_id' => $board->id
        ]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson("/api/columns/{$column->id}/cards", [
                'title' => 'Test Card',
                'order' => 1
            ]);

        $response->assertStatus(201);
    });

    it('allows verified user to invite users', function () {
        $userData = $this->createUserWithToken([
            'email_verified_at' => now()
        ]);
        $user = $userData['user'];
        $token = $userData['token'];

        $organization = Organization::factory()->create([
            'owner_id' => $user->id
        ]);
        $user->organizations()->attach($organization->id, ['role' => 'admin']);
        $user->update(['current_organization_id' => $organization->id]);

        $response = $this->withApiToken($token)
            ->withHeaders(['X-Organization-ID' => $organization->id])
            ->postJson("/api/organizations/{$organization->id}/invite", [
                'email' => 'newuser@example.com'
            ]);

        $response->assertStatus(200);
    });
});