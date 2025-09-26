<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Organization Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    });

    it('can create an organization with name only', function () {
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Organization created successfully!',
                'organization' => [
                    'name' => 'Test Organization',
                    'subdomain' => null,
                    'settings' => [],
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'owner_id' => $this->user->id,
            'subdomain' => null,
        ]);
    });

    it('can create an organization with subdomain', function () {
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Organization created successfully!',
                'organization' => [
                    'name' => 'Test Organization',
                    'subdomain' => 'test-org',
                    'settings' => [],
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'owner_id' => $this->user->id,
            'subdomain' => 'test-org',
        ]);
    });

    it('can create an organization with settings', function () {
        $settings = [
            'theme' => 'dark',
            'timezone' => 'UTC',
            'features' => ['boards', 'cards', 'collaboration'],
        ];

        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'settings' => $settings,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Organization created successfully!',
                'organization' => [
                    'name' => 'Test Organization',
                    'subdomain' => null,
                    'settings' => $settings,
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Test Organization',
            'owner_id' => $this->user->id,
            'subdomain' => null,
        ]);
    });

    it('can create an organization with all fields', function () {
        $settings = [
            'theme' => 'light',
            'timezone' => 'America/New_York',
            'features' => ['boards', 'cards'],
        ];

        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Complete Organization',
            'subdomain' => 'complete-org',
            'settings' => $settings,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Organization created successfully!',
                'organization' => [
                    'name' => 'Complete Organization',
                    'subdomain' => 'complete-org',
                    'settings' => $settings,
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'Complete Organization',
            'owner_id' => $this->user->id,
            'subdomain' => 'complete-org',
        ]);
    });

    it('validates subdomain format', function () {
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'subdomain' => 'invalid_subdomain!',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates subdomain length', function () {
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'subdomain' => 'ab',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates subdomain uniqueness', function () {
        $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'First Organization',
            'subdomain' => 'unique-subdomain',
        ]);
        
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Second Organization',
            'subdomain' => 'unique-subdomain',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates reserved subdomains', function () {
        $reservedSubdomains = ['www', 'api', 'admin', 'app'];

        foreach ($reservedSubdomains as $subdomain) {
            $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
                'name' => 'Test Organization',
                'subdomain' => $subdomain,
            ]);

            $response
                ->assertStatus(422)
                ->assertJsonValidationErrors(['subdomain']);
        }
    });

    it('validates settings as array', function () {
        $response = $this->withApiToken($this->token)->postJson('/api/organizations', [
            'name' => 'Test Organization',
            'settings' => 'not-an-array',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['settings']);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/organizations', [
            'name' => 'Test Organization',
        ]);

        $response->assertStatus(401);
    });
});
