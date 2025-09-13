<?php

use App\Models\Board;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Board Management', function () {
    beforeEach(function () {
        $this->user = User::factory()->create([
            'email' => 'john.doe@example.com',
        ]);

        $this->organization = Organization::factory()->create([
            'name' => 'Test Organization',
            'owner_id' => $this->user->id,
        ]);

        $this->user->update([
            'current_organization_id' => $this->organization->id,
        ]);
    });

    it('can create a board with authenticated user', function () {
        $response = $this->actingAs($this->user)->postJson('/api/boards', [
            'name' => 'New Board',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Board created successfully!',
                'data' => [
                    'id' => 1,
                    'name' => 'New Board',
                ],
            ]);
    });

    it('cannot create a board with unauthenticated user', function () {
        $userWithoutOrg = User::factory()->create([
            'email' => 'john.doe.without.org@example.com',
        ]);

        $response = $this->actingAs($userWithoutOrg)->postJson('/api/boards', [
            'name' => 'New Board',
        ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => 'User does not belong to any organization.'
            ]);
    });

    it('can list boards for an authenticated user', function () {
        Board::factory()->count(3)->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->actingAs($this->user)
            ->getJson('/api/boards')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['name', 'id']
                ]
            ])
            ->assertJsonCount(3, 'data');
    });

    it('returns empty array when organization has no boards', function () {
        $this->actingAs($this->user)
            ->getJson('/api/boards')
            ->assertStatus(200)
            ->assertJson([
                'data' => []
            ]);
    });

    it('can update a board name', function () {
        $board = Board::factory()->create([
            'name' => 'Initial Board',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/boards/{$board->id}", [
                'name' => 'Updated Board Name',
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Board updated successfully!',
                'data' => [
                    'id' => $board->id,
                    'name' => 'Updated Board Name',
                ],
            ]);

        $this->assertDatabaseHas('boards', [
            'id' => $board->id,
            'name' => 'Updated Board Name',
        ]);
    });

    it('cannot update a board to a name that already exists in the organization', function () {
        Board::factory()->create([
            'name' => 'Existing Board',
            'organization_id' => $this->organization->id,
        ]);

        $boardToUpdate = Board::factory()->create([
            'name' => 'Board To Update',
            'organization_id' => $this->organization->id,
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/boards/{$boardToUpdate->id}", [
                'name' => 'Existing Board',
            ]);

        $response
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Board name already exists in this organization.'
            ]);
    });

    it('can list board with its columns and cards', function () {
        $board = Board::factory()->create([
            'name' => 'Board with Cards',
            'organization_id' => $this->organization->id,
        ]);

        $column = $board->columns()->create([
            'name' => 'To Do',
            'order' => 1,
        ]);

        $column->cards()->createMany([
            ['title' => 'Task 1', 'description' => 'Description 1', 'order' => 1],
            ['title' => 'Task 2', 'description' => 'Description 2', 'order' => 2],
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/boards/{$board->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'columns' => [
                        '*' => [
                            'id',
                            'name',
                            'order',
                            'cards' => [
                                '*' => ['id', 'title', 'description', 'order']
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(1, 'data.columns')
            ->assertJsonCount(2, 'data.columns.0.cards');
    });
});
