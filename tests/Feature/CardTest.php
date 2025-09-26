<?php

use App\Models\Board;
use App\Models\BoardColumn;
use App\Models\Card;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Card Management', function () {
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

        $this->column = BoardColumn::factory()->create([
            'board_id' => $this->board->id,
        ]);

        $this->token = $this->user->createToken('test-token')->plainTextToken;
    });

    it('can create a card in a column', function () {
        $response = $this->withApiToken($this->token)
            ->postJson("/api/columns/{$this->column->id}/cards", [
            'title' => 'New Card',
            'description' => 'Card description',
            'order' => 1,
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Card created successfully!',
                'data' => [
                    'column_id' => $this->column->id,
                    'title' => 'New Card',
                    'description' => 'Card description',
                    'order' => 1,
                ],
            ]);

        $this->assertIsInt($response->json('data.id'));
        $this->assertNotNull($response->json('data.created_at'));
        $this->assertNotNull($response->json('data.updated_at'));
    });

    it('can update a card', function () {
        $card = Card::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Initial Title',
            'description' => 'Initial Description',
            'order' => 1,
        ]);

        $response = $this->withApiToken($this->token)
            ->putJson("/api/cards/{$card->id}", [
                'title' => 'Updated Title',
                'description' => 'Updated Description',
                'order' => 2,
            ]);
            
        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Card updated successfully!',
                'data' => [
                    'id' => $card->id,
                    'column_id' => $this->column->id,
                    'title' => 'Updated Title',
                    'description' => 'Updated Description',
                    'order' => 2,
                ],
            ]);

        $this->assertNotNull($response->json('data.created_at'));
        $this->assertNotNull($response->json('data.updated_at'));
    });

    it('can delete a card', function () {
        $card = Card::factory()->create([
            'column_id' => $this->column->id,
            'title' => 'Card to be deleted',
            'description' => 'This card will be deleted',
            'order' => 1,
        ]);

        $response = $this->withApiToken($this->token)
            ->deleteJson("/api/cards/{$card->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Card deleted successfully!',
            ]);

        $this->assertDatabaseMissing('cards', [
            'id' => $card->id,
        ]);
    });
});
