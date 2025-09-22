<?php

namespace App\Application\Column\Services;

use RuntimeException;
use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Domain\Column\Services\ColumnDomainService;
use App\Models\User;

class ColumnService
{
    public function __construct(
        private ColumnRepositoryInterface $columnRepository,
        private ColumnDomainService $columnDomainService,
    ) {
    }

    public function list(User $user, int $boardId): array
    {
        $this->ensureUserCanAccessBoard($user, $boardId);

        return $this->columnRepository->findByBoard($boardId);
    }

    public function create(User $user, array $columnData): array
    {
        $this->ensureUserCanAccessBoard($user, $columnData['boardId']);

        $columnData['order'] = $this->columnRepository->fetchMaxOrderInBoard($columnData['boardId']);
        $columnData['board_id'] = $columnData['boardId'];

        return $this->columnRepository->create($columnData);
    }

    public function createBulk(User $user, array $columnsData): array
    {
        if (empty($columnsData)) {
            return [];
        }

        $boardId = $columnsData[0]['boardId'];
        $this->ensureUserCanAccessBoard($user, $boardId);

        $maxOrder = $this->columnRepository->fetchMaxOrderInBoard($boardId);
        foreach ($columnsData as $index => &$columnData) {
            if (!isset($columnData['order'])) {
                $columnData['order'] = $maxOrder + $index;
            }
            $columnData['board_id'] = $boardId;
        }

        return $this->columnRepository->createBulk($columnsData);
    }

    public function update(?User $user, array $columnData): array
    {
        $this->ensureUserCanAccessBoard($user, $columnData['boardId']);

        $columnData['board_id'] = $columnData['boardId'];

        return $this->columnRepository->update($columnData);
    }

    private function ensureUserCanAccessBoard(?User $user, int $boardId): void
    {
        if (!$this->columnDomainService->canAccessBoard($boardId, $user->current_organization_id)) {
            throw new RuntimeException('User does not have access to this board.');
        }
    }
}
