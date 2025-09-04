<?php

namespace App\Domain\Card\Repositories;

use App\Domain\Card\Entities\Card;

interface CardRepositoryInterface
{
    public function getCardsByColumnId(int $columnId): array;
    public function save(Card $card): Card;
    public function findById(int $cardId): ?Card;
}
