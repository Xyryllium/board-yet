<?php

namespace App\Domain\Card\Entities;

use DateTime;

class Card
{
    public function __construct(
        public ?int $cardId,
        public int $columnId,
        public string $title,
        public ?string $description,
        public ?int $order,
        public ?DateTime $createdAt = null,
        public ?DateTime $updatedAt = null,
    ) {
    }

    public static function create(array $data): self
    {
        return new self(
            cardId: null,
            columnId: $data['column_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            order: $data['order'] ?? null,
            createdAt: new DateTime(),
            updatedAt: new DateTime(),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cardId: $data['id'] ?? null,
            columnId: $data['column_id'],
            title: $data['title'],
            description: $data['description'] ?? null,
            order: $data['order'] ?? null,
            createdAt: $data['updated_at'] ? new DateTime($data['updated_at']) : null,
            updatedAt: $data['updated_at'] ? new DateTime($data['updated_at']) : null,
        );
    }

    public function update(array $data): void
    {
        if (isset($data['column_id'])) {
            $this->columnId = $data['column_id'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        }
        if (array_key_exists('description', $data)) {
            $this->description = $data['description'];
        }
        if (array_key_exists('order', $data)) {
            $this->order = $data['order'];
        }
        $this->updatedAt = new DateTime();
    }

    public function getCardId(): int
    {
        return $this->cardId;
    }

    public function getColumnId(): int
    {
        return $this->columnId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->cardId,
            'column_id'  => $this->columnId,
            'title'      => $this->title,
            'description' => $this->description,
            'order'      => $this->order,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
