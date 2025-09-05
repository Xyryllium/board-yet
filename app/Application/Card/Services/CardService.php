<?php

namespace App\Application\Card\Services;

use RuntimeException;
use App\Domain\Card\Entities\Card;
use App\Domain\Card\Repositories\CardRepositoryInterface;

class CardService
{
    public function __construct(private readonly CardRepositoryInterface $cardRepository)
    {
    }

    public function getCardsByColumnId(int $columnId): array
    {
        return $this->cardRepository->getCardsByColumnId($columnId);
    }

    public function createCard(array $cardData): Card
    {
        $card = Card::create($cardData);
        return $this->cardRepository->save($card);
    }

    public function findCardById(int $cardId): ?Card
    {
        $card = $this->cardRepository->findById($cardId);

        if (!$card) {
            throw new RuntimeException("Card not found");
        }

        return $card;
    }

    public function updateCard(int $cardId, array $cardData): Card
    {
        $card = $this->findCardById($cardId);
        $card->update($cardData);

        return $this->cardRepository->save($card);
    }

    public function deleteCard(int $cardId): void
    {
        $card = $this->findCardById($cardId);
        $this->cardRepository->delete($card);
    }
}
