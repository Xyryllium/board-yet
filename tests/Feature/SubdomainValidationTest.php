<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token')->plainTextToken;
});

describe('Subdomain Validation', function () {
    it('validates available subdomain', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=stark');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => true,
                'error' => null
            ]);
    });

    it('validates unavailable subdomain', function () {
        Organization::factory()->create(['subdomain' => 'stark']);

        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=stark');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => false,
                'error' => 'Subdomain is already taken'
            ]);
    });

    it('validates subdomain with exclude parameter', function () {
        $organization = Organization::factory()->create(['subdomain' => 'stark']);

        $response = $this->withApiToken($this->token)
            ->getJson("/api/organizations/subdomain/validate?subdomain=stark&exclude={$organization->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => true,
                'error' => null
            ]);
    });

    it('validates invalid subdomain format', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=st@rk');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => false,
                'error' => 'Invalid format'
            ]);
    });

    it('validates reserved subdomain', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=www');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'available' => false,
                'error' => 'Reserved'
            ]);
    });

    it('validates subdomain that is too short', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=st');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates subdomain that is too long', function () {
        $longSubdomain = str_repeat('a', 64); // 64 characters
        $response = $this->withApiToken($this->token)
            ->getJson("/api/organizations/subdomain/validate?subdomain={$longSubdomain}");

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/organizations/subdomain/validate?subdomain=stark');

        $response->assertStatus(401);
    });

    it('requires subdomain parameter', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates exclude parameter exists', function () {
        $response = $this->withApiToken($this->token)
            ->getJson('/api/organizations/subdomain/validate?subdomain=stark&exclude=999999');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['exclude']);
    });
});
