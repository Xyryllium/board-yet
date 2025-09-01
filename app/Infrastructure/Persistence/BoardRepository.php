<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Models\Board;

class BoardRepository implements BoardRepositoryInterface
{

    public function save(array $boardData): void
    {
        Board::create([
            'name' => $boardData['name'],
            'organization_id' => $boardData['organization_id'],
        ]);
    }
}
