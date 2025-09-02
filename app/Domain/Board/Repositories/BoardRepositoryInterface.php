<?php

namespace App\Domain\Board\Repositories;

interface BoardRepositoryInterface
{
    public function create(array $data): array;
    public function update(int $boardId, array $data): array;
    public function all(int $organizationId): array;
    public function findByNameAndOrganizationId(string $name, int $organizationId): ?array;
}
