<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Column Management', function () {

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

        $this->organization->users()->attach($this->user->id, ['role' => 'admin']);

        $this->board = Board::factory()->create([
            'organization_id' => $this->organization->id,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    });

    it('can list columns based on board', function () {
        BoardColumn::factory()->count(3)->create([
            'board_id' => $this->board->id,
        ]);
        
        $response = $this->withApiToken($this->token)
                    ->getJson("/api/boards/{$this->board->id}/columns");

        $response
            ->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    it('cannot list columns for a board from another organization', function () {
        $otherOrganization = Organization::factory()->create([
            'owner_id' => $this->user->id,
        ]);
        $otherBoard = Board::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->withApiToken($this->token)
                    ->getJson("/api/boards/{$otherBoard->id}/columns");

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'User does not have access to this board.'
            ]);
    });

    it('can create a column with authenticated user', function () {
        $response = $this->withApiToken($this->token)
            ->postJson("/api/columns", [
                'boardId' => $this->board->id,
                'columns' => [
                    [
                        'id' => 1,
                        'name' => 'New Column',
                        'order' => 1,
                    ],
                ],
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Columns created successfully!',
                'data' => [
                    [
                        'id' => $response->json('data.0.id'),
                        'name' => 'New Column',
                        'order' => 1,
                        'created_at' => $response->json('data.0.created_at'),
                        'updated_at' => $response->json('data.0.updated_at'),
                    ],
                ],
            ]);
    });

    it('cannot create a column for a board from another organization', function () {
        $otherOrganization = Organization::factory()->create([
            'owner_id' => $this->user->id,
        ]);
        $otherBoard = Board::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);

        $response = $this->withApiToken($this->token)
            ->postJson("/api/columns", [
                'boardId' => $otherBoard->id,
                'columns' => [
                    [
                        'id' => 1,
                        'name' => 'New Column',
                        'order' => 1,
                    ],
                ],
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'error' => 'User does not have access to this board.'
            ]);
    });

    it('cannot create a column with unauthenticated user', function () {
        $response = $this->postJson("/api/columns", [
            'boardId' => $this->board->id,
            'columns' => [
                [
                    'id' => 1,
                    'name' => 'New Column',
                    'order' => 1,
                ],
            ],
        ]);

        $response
            ->assertStatus(401);
    });

    it('can update a column with authenticated user', function () {
        $column = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Initial Column',
            'order' => 1,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/$column->id", [
                'boardId' => $this->board->id,
                'name' => 'Updated Column',
                'order' => 2,
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Column updated successfully!',
                'data' => [
                    'id' => $column->id,
                    'name' => 'Updated Column',
                    'order' => 2,
                    'created_at' => $response->json('data.created_at'),
                    'updated_at' => $response->json('data.updated_at'),
                ],
            ]);
    });

    it('cannot update a column for a board from another organization', function () {
        $otherOrganization = Organization::factory()->create([
            'owner_id' => $this->user->id,
        ]);
        $otherBoard = Board::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);
        $column = BoardColumn::factory()->create([
            'board_id' => $otherBoard->id,
            'name' => 'Initial Column',
            'order' => 1,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/$column->id", [
            'boardId' => $otherBoard->id,
            'name' => 'Updated Column',
            'order' => 2,
        ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'User does not have access to this board.'
            ]);
    });
});
