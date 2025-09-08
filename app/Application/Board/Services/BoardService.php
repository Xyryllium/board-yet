<?php

namespace App\Application\Board\Services;

use RuntimeException;
use App\Domain\Board\Exceptions\BoardNotFoundException;
use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Models\User;

class BoardService
{
    public function __construct(private BoardRepositoryInterface $boardRepository)
    {
    }

    public function list(User $user): array
    {
        return $this->boardRepository->all($user->current_organization_id);
    }

    public function getBoardWithCards(int $boardId): array
    {
        $board = $this->boardRepository->findByIdWithCards($boardId);

        if (!$board) {
            throw BoardNotFoundException::withId($boardId);
        }

        return $board;
    }

    public function create(?User $user, array $boardData): array
    {
        $this->ensureUserInOrganization($user);

        $boardData['organization_id'] = $user->current_organization_id;

        return $this->boardRepository->create($boardData);
    }

    public function update(?User $user, array $boardData, int $boardId): array
    {
        $this->ensureUserInOrganization($user);

        $boardData['organization_id'] = $user->current_organization_id;

        if (isset($boardData['name'])) {
            $this->validateBoardName($boardData);
        }

        return $this->boardRepository->update($boardId, $boardData);
    }

    private function ensureUserInOrganization(?User $user): void
    {
        if (!$user || !$user->currentOrganization) {
            throw new RuntimeException('User does not belong to any organization.');
        }
    }

    private function validateBoardName(array $boardData): void
    {
        $boardExist = $this->boardRepository->findByNameAndOrganizationId(
            $boardData['name'],
            $boardData['organization_id']
        );

        if ($boardExist) {
            throw new RuntimeException('Board name already exists in this organization.');
        }
    }
}
