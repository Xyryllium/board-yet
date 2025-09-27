<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Column\Entities\Column;
use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Models\BoardColumn;

class ColumnRepository implements ColumnRepositoryInterface
{
    public function findByBoard(int $boardId): array
    {
        /** @phpstan-ignore-next-line */
        $columns = BoardColumn::where('board_id', $boardId)
            ->whereHas('board', function ($query) {
                $query->where('organization_id', request()->attributes->get('organization_id'));
            })
            ->orderBy('order')
            ->get();

        return $columns->map(function ($board) {
            return $this->toDomain($board);
        })->toArray();
    }

    public function fetchMaxOrderInBoard(int $boardId): int
    {
        /** @phpstan-ignore-next-line */
        return (BoardColumn::where('board_id', $boardId)
            ->whereHas('board', function ($query) {
                $query->where('organization_id', request()->attributes->get('organization_id'));
            })
            ->max('order') ?? 0) + 1;
    }

    public function create(array $columnData): array
    {
        $column = BoardColumn::create($columnData);

        return $this->toDomain($column)->toArray();
    }

    public function createBulk(array $columnsData): array
    {
        $createdColumns = [];

        foreach ($columnsData as $columnData) {
            $column = BoardColumn::create($columnData);
            $createdColumns[] = $this->toDomain($column)->toArray();
        }

        return $createdColumns;
    }

    public function findById(int $columnId): ?Column
    {
        /** @phpstan-ignore-next-line */
        $column = BoardColumn::where('id', $columnId)
            ->whereHas('board', function ($query) {
                $query->where('organization_id', request()->attributes->get('organization_id'));
            })
            ->first();
        return $column ? $this->toDomain($column) : null;
    }

    public function delete(Column $column): void
    {
        $columnModel = BoardColumn::find($column->columnId);

        if ($columnModel) {
            $columnModel->delete();
        }
    }

    public function update(array $columnData): array
    {
        /** @phpstan-ignore-next-line */
        $column = BoardColumn::where('id', $columnData['id'])
                ->where('board_id', $columnData['board_id'])
                ->whereHas('board', function ($query) {
                    $query->where('organization_id', request()->attributes->get('organization_id'));
                })
                ->firstOrFail();

        $column->update([
            'name' => $columnData['name'],
            'order' => $columnData['order'],
        ]);

        return $this->toDomain($column->fresh())->toArray();
    }

    public function reorderBulk(array $columns): void
    {
        foreach ($columns as $columnData) {
            /** @phpstan-ignore-next-line */
            BoardColumn::where('id', $columnData['id'])
                ->whereHas('board', function ($query) {
                    $query->where('organization_id', request()->attributes->get('organization_id'));
                })
                ->update(['order' => $columnData['order']]);
        }
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
