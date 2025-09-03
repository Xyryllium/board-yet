<?php

namespace App\Domain\Column\Entities;

class Column
{
    public function __construct(
        public ?int $columnId,
        public string $name,
        public int $order,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->columnId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->columnId,
            'name' => $this->name,
            'order' => $this->order,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
