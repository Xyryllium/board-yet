<?php

namespace App\Domain\Column\Repositories;

interface ColumnRepositoryInterface
{
    public function findByBoard(int $boardId): array;
    public function fetchMaxOrderInBoard(int $boardId): int;
    public function create(array $columnData): array;
    public function update(array $columnData): array;
}
