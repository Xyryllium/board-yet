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

    it('can reorder columns with authenticated user', function () {
        $column1 = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Column 1',
            'order' => 0,
        ]);
        $column2 = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Column 2',
            'order' => 1,
        ]);
        $column3 = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Column 3',
            'order' => 2,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/reorder", [
                'boardId' => $this->board->id,
                'columns' => [
                    ['id' => $column1->id, 'order' => 2],
                    ['id' => $column2->id, 'order' => 0],
                    ['id' => $column3->id, 'order' => 1],
                ],
            ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Columns reordered successfully!',
                'data' => [
                    ['id' => $column1->id, 'order' => 2],
                    ['id' => $column2->id, 'order' => 0],
                    ['id' => $column3->id, 'order' => 1],
                ],
            ]);

        $this->assertDatabaseHas('columns', [
            'id' => $column1->id,
            'order' => 2,
        ]);
        $this->assertDatabaseHas('columns', [
            'id' => $column2->id,
            'order' => 0,
        ]);
        $this->assertDatabaseHas('columns', [
            'id' => $column3->id,
            'order' => 1,
        ]);
    });

    it('cannot reorder columns for a board from another organization', function () {
        $otherOrganization = Organization::factory()->create([
            'owner_id' => $this->user->id,
        ]);
        $otherBoard = Board::factory()->create([
            'organization_id' => $otherOrganization->id,
        ]);
        $column = BoardColumn::factory()->create([
            'board_id' => $otherBoard->id,
            'name' => 'Column',
            'order' => 0,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/reorder", [
                'boardId' => $otherBoard->id,
                'columns' => [
                    ['id' => $column->id, 'order' => 1],
                ],
            ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => 'User does not have access to this board.'
            ]);
    });

    it('cannot reorder columns with unauthenticated user', function () {
        $column = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Column',
            'order' => 0,
        ]);

        $response = $this->putJson("/api/columns/reorder", [
            'boardId' => $this->board->id,
            'columns' => [
                ['id' => $column->id, 'order' => 1],
            ],
        ]);

        $response->assertStatus(401);
    });

    it('validates reorder request data', function () {
        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/reorder", [
                'boardId' => $this->board->id,
                'columns' => [
                    ['id' => 999, 'order' => 1],
                ],
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['columns.0.id']);
    });

    it('validates that columns belong to the specified board', function () {
        $otherBoard = Board::factory()->create([
            'organization_id' => $this->organization->id,
        ]);
        $otherColumn = BoardColumn::factory()->create([
            'board_id' => $otherBoard->id,
            'name' => 'Other Board Column',
            'order' => 0,
        ]);
        $currentColumn = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
            'name' => 'Current Board Column',
            'order' => 0,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/columns/reorder", [
                'boardId' => $this->board->id,
                'columns' => [
                    ['id' => $otherColumn->id, 'order' => 1],
                    ['id' => $currentColumn->id, 'order' => 0],
                ],
            ]);

        $response
            ->assertStatus(403)
            ->assertJson([
                'message' => "Column with ID {$otherColumn->id} does not belong to board {$this->board->id}."
            ]);
    });
});
