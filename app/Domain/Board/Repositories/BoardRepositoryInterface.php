<?php

namespace App\Domain\Board\Repositories;

interface BoardRepositoryInterface
{
    public function save(array $data): void;
}
