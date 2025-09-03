<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Column\Entities\Column;
use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Models\BoardColumn;

class ColumnRepository implements ColumnRepositoryInterface
{
    public function findByBoard(int $boardId): array
    {
        $columns = BoardColumn::where('board_id', $boardId)->orderBy('order')->get();

        return $columns->map(function ($board) {
            return $this->toDomain($board);
        })->toArray();
    }

    public function fetchMaxOrderInBoard(int $boardId): int
    {
        return BoardColumn::where('board_id', $boardId)->max('order') + 1 ?? 1;
    }

    public function create(array $columnData): array
    {
        $column = BoardColumn::create($columnData);

        return $this->toDomain($column)->toArray();
    }

    public function update(array $columnData): array
    {
        $column = BoardColumn::where('id', $columnData['id'])
                ->where('board_id', $columnData['board_id'])
                ->firstOrFail();

        $column->update([
            'name' => $columnData['name'],
            'order' => $columnData['order'],
        ]);

        return $this->toDomain($column->fresh())->toArray();
    }

    private function toDomain(BoardColumn $column): Column
    {
        return new Column(
            $column->id,
            $column->name,
            $column->order,
            $column->created_at,
            $column->updated_at,
        );
    }
}