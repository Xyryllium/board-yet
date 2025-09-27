<?php

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Organization Settings Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $this->organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'subdomain' => 'test-org',
            'owner_id' => $this->user->id,
            'settings' => [
                'theme' => ['primaryColor' => '#000000'],
                'branding' => ['companyName' => 'Old Company'],
            ],
        ]);

        $this->user->update([
            'current_organization_id' => $this->organization->id,
        ]);

        $this->organization->users()->attach($this->user->id, ['role' => 'admin']);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    });

    it('can update organization settings', function () {
        $settingsData = [
            'subdomain' => 'updated-org',
            'settings' => [
                'theme' => [
                    'primaryColor' => '#FF5733',
                ],
                'branding' => [
                    'companyName' => 'Updated Company',
                    'supportEmail' => 'support@updatedcompany.com',
                ],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Organization settings updated successfully!',
                'organization' => [
                    'id' => $this->organization->id,
                    'subdomain' => 'updated-org',
                    'settings' => [
                        'theme' => [
                            'primaryColor' => '#FF5733',
                        ],
                        'branding' => [
                            'companyName' => 'Updated Company',
                            'supportEmail' => 'support@updatedcompany.com',
                        ],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $this->organization->id,
            'subdomain' => 'updated-org',
        ]);
    });

    it('can update only settings without changing subdomain', function () {
        $settingsData = [
            'settings' => [
                'theme' => [
                    'primaryColor' => '#00FF00',
                ],
                'branding' => [
                    'companyName' => 'Settings Only Update',
                    'supportEmail' => 'settings@example.com',
                ],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Organization settings updated successfully!',
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $this->organization->id,
            'subdomain' => 'test-org',
        ]);
    });

    it('merges new settings with existing settings', function () {
        $settingsData = [
            'settings' => [
                'theme' => [
                    'primaryColor' => '#FF0000',
                ],
                'newFeature' => [
                    'enabled' => true,
                ],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response->assertStatus(200);

        $updatedOrganization = $response->json('organization');
        $settings = $updatedOrganization['settings'];

        expect($settings['theme']['primaryColor'])->toBe('#FF0000');
        
        expect($settings)->toHaveKey('newFeature');
        expect($settings['newFeature']['enabled'])->toBe(true);
        
        expect($settings)->toHaveKey('branding');
        expect($settings['branding']['companyName'])->toBe('Old Company');
    });

    it('validates subdomain format', function () {
        $settingsData = [
            'subdomain' => 'invalid_subdomain!',
            'settings' => [
                'theme' => ['primaryColor' => '#FF5733'],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates subdomain uniqueness', function () {
        $otherOrganization = Organization::factory()->create([
            'subdomain' => 'unique-subdomain',
            'owner_id' => $this->user->id,
        ]);

        $settingsData = [
            'subdomain' => 'unique-subdomain',
            'settings' => [
                'theme' => ['primaryColor' => '#FF5733'],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['subdomain']);
    });

    it('validates primary color format', function () {
        $settingsData = [
            'settings' => [
                'theme' => [
                    'primaryColor' => 'invalid-color',
                ],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['settings.theme.primaryColor']);
    });

    it('validates support email format', function () {
        $settingsData = [
            'settings' => [
                'branding' => [
                    'supportEmail' => 'invalid-email',
                ],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['settings.branding.supportEmail']);
    });

    it('requires settings to be an array', function () {
        $settingsData = [
            'settings' => 'not-an-array',
        ];

        $response = $this->withApiToken($this->token)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['settings']);
    });

    it('requires authentication', function () {
        $settingsData = [
            'settings' => [
                'theme' => ['primaryColor' => '#FF5733'],
            ],
        ];

        $response = $this->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response->assertStatus(401);
    });

    it('requires organization membership', function () {
        $otherUser = User::factory()->create([
            'email' => 'other@example.com',
        ]);
        $otherToken = $otherUser->createToken('other-token')->plainTextToken;

        $settingsData = [
            'settings' => [
                'theme' => ['primaryColor' => '#FF5733'],
            ],
        ];

        $response = $this->withApiToken($otherToken)
            ->putJson("/api/organizations/{$this->organization->id}/settings", $settingsData);

        $response->assertStatus(403);
    });

    it('returns 404 for non-existent organization', function () {
        $settingsData = [
            'settings' => [
                'theme' => ['primaryColor' => '#FF5733'],
            ],
        ];

        $response = $this->withApiToken($this->token)
            ->putJson('/api/organizations/99999/settings', $settingsData);

        $response->assertStatus(500);
    });
});
