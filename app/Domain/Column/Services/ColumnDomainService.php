<?php

namespace App\Domain\Column\Services;

use App\Domain\Board\Repositories\BoardRepositoryInterface;

class ColumnDomainService
{
    public function __construct(private BoardRepositoryInterface $boardRepository)
    {
        
    }
    
    public function canAccessBoard(int $boardId, int $organizationId): bool
    {
        $board = $this->boardRepository->findByIdAndOrganizationId($boardId, $organizationId);
        return $board !== null;
    }   
}