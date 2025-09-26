<?php

namespace App\Domain\Column\Repositories;

use App\Domain\Column\Entities\Column;

interface ColumnRepositoryInterface
{
    public function findByBoard(int $boardId): array;
    public function fetchMaxOrderInBoard(int $boardId): int;
    public function create(array $columnData): array;
    public function createBulk(array $columnsData): array;
    public function update(array $columnData): array;
    public function findById(int $columnId): ?Column;
    public function delete(Column $column): void;
    public function reorderBulk(array $columns): void;
}
