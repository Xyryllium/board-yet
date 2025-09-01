<?php

namespace App\Application\Board\Services;

use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Models\User;

class BoardService
{
    public function __construct(private BoardRepositoryInterface $boardRepository)
    {
    }

    public function create(?User $user, array $boardData): array
    {
        if (!$user) {
            throw new \RuntimeException('User is not authenticated.');
        }

        if (!$user->currentOrganization()) {
            throw new \RuntimeException('User does not belong to any organization.');
        }

        $boardData['organization_id'] = $user->current_organization_id;

        $this->boardRepository->save($boardData);

        return $boardData;
    }
}
