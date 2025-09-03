<?php

namespace App\Application\Column\Services;

use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Domain\Column\Services\ColumnDomainService;
use App\Models\User;

class ColumnService
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository,
        private ColumnDomainService $columnDomainService,
    ){
    }

    public function list(User $user, int $boardId): array
    {
        $this->ensureUserCanAccessBoard($user, $boardId);

        return $this->columnRepository->findByBoard($boardId);
    }

    public function create(User $user, array $columnData): array
    {
        $this->ensureUserCanAccessBoard($user, $columnData['board_id']);

        $columnData['order'] = $this->columnRepository->fetchMaxOrderInBoard($columnData['board_id']);

        return $this->columnRepository->create($columnData);
    }

    public function update(?User $user, array $columnData): array
    {
        $this->ensureUserCanAccessBoard($user, $columnData['board_id']);

        return $this->columnRepository->update($columnData);
    }

    private function ensureUserCanAccessBoard(?User $user, int $boardId): void
    {
        if (!$this->columnDomainService->canAccessBoard($boardId, $user->current_organization_id)) {
            throw new \RuntimeException('User does not have access to this board.');
        }
    }
}