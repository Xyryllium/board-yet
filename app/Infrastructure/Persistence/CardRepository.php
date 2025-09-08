<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Card\Entities\Card as EntitiesCard;
use App\Domain\Card\Repositories\CardRepositoryInterface;
use App\Models\Card;

class CardRepository implements CardRepositoryInterface
{
    public function getCardsByColumnId(int $columnId): array
    {
        $cards = Card::where('column_id', $columnId)->get();

        //TODO: Implement entity mapping here if needed
        return $cards->toArray();
    }

    public function findById(int $cardId): ?EntitiesCard
    {
        $card = Card::find($cardId);
        return $card ? $this->toDomain($card) : null;
    }

    public function save(EntitiesCard $card): EntitiesCard
    {
        $cardModel = Card::updateOrCreate(['id' => $card->cardId], $card->toArray());

        return $this->toDomain($cardModel->fresh());
    }

    public function delete(EntitiesCard $card): void
    {
        $cardModel = Card::find($card->cardId);

        if ($cardModel) {
            $cardModel->delete();
        }
    }

    private function toDomain(Card $card): EntitiesCard
    {
        return EntitiesCard::fromArray([
            'id' => $card->id,
            'column_id' => $card->column_id,
            'title' => $card->title,
            'description' => $card->description,
            'order' => $card->order,
            'created_at' => $card->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $card->updated_at?->format('Y-m-d H:i:s'),
        ]);
    }
}
