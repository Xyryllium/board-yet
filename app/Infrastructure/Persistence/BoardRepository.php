<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Board\Entities\Board as EntitiesBoard;
use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Models\Board;

class BoardRepository implements BoardRepositoryInterface
{
    public function all(int $organizationId): array
    {
        /** @phpstan-ignore-next-line */
        $boards = Board::where('organization_id', $organizationId)
            ->with(['columns' => function ($query) {
                    $query->orderBy('order');
            }])
            ->orderBy('created_at', 'asc')
            ->get();

        return $boards->map(function ($board) {
            return $this->toDomain($board)->toArray();
        })->toArray();
    }

    public function findByIdWithCards(int $boardId, int $organizationId): ?array
    {
        /** @phpstan-ignore-next-line */
        $boardWithCards = Board::where('id', $boardId)
            ->where('organization_id', $organizationId)
            ->with([
                'columns' => function ($query) {
                    $query->orderBy('order');
                },
                'columns.cards' => function ($query) {
                    $query->orderBy('order');
                },
            ])->first();

        return $boardWithCards ? $this->toDomain($boardWithCards)->toArray() : null;
    }

    public function create(array $boardData): array
    {
        $board = Board::create([
            'name' => $boardData['name'],
            'organization_id' => $boardData['organization_id'],
        ]);

        return $this->toDomain($board)->toArray();
    }

    public function update(int $boardId, array $boardData): array
    {
        $board = Board::where('id', $boardId)
                ->where('organization_id', $boardData['organization_id'])
                ->firstOrFail();

        $board->update([
            'name' => $boardData['name'],
        ]);

        return $this->toDomain($board->fresh())->toArray();
    }

    public function findByNameAndOrganizationId(string $name, int $organizationId): ?array
    {
        $board = Board::where('name', $name)
                ->where('organization_id', $organizationId)
                ->first();

        return $board ? $this->toDomain($board)->toArray() : null;
    }

    public function findByIdAndOrganizationId(int $boardId, int $organizationId): ?array
    {
        $board = Board::where('id', $boardId)
                ->where('organization_id', $organizationId)
                ->first();

        return $board ? $this->toDomain($board)->toArray() : null;
    }

    private function toDomain(Board $board): EntitiesBoard
    {
        return EntitiesBoard::fromArray([
            'id' => $board->id,
            'name' => $board->name,
            /** @phpstan-ignore-next-line */
            'columns' => $board->columns?->map(fn ($column) => [
                'id' => $column->id,
                'name' => $column->name,
                'order' => $column->order,
                'cards' => $column->cards?->map(fn ($card) => [
                        'id' => $card->id,
                        'title' => $card->title,
                        'description' => $card->description,
                        'order' => $card->order,
                ])->toArray() ?? [],
            ])->toArray() ?? [],
            'created_at' => $board->created_at?->toISOString(),
            'updated_at' => $board->updated_at?->toISOString(),
        ]);
    }
}
