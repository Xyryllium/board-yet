<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Board\Entities\Board as EntitiesBoard;
use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Models\Board;

class BoardRepository implements BoardRepositoryInterface
{
    public function all(int $organizationId): array
    {
        $boards = Board::where('organization_id', $organizationId)->get();

        return $boards->map(function ($board) {
            return $this->toDomain($board);
        })->toArray();
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
        return new EntitiesBoard(
            name: $board->name,
            boardId: $board->id,
        );
    }
}
