<?php

namespace App\Domain\Board\Entities;

use DateTime;

class Board
{
    public function __construct(
        public ?int $boardId,
        public string $name,
        public ?string $description = null,
        public array $columns = [],
        public ?DateTime $createdAt = null,
        public ?DateTime $updatedAt = null,
    ) {
    }

    public static function create(array $data): self
    {
        return new self(
            boardId: $data['id'] ?? null,
            name: $data['name'],
            description: $data['description'] ?? null,
            columns: $data['columns'] ?? [],
            createdAt: isset($data['created_at']) ? new DateTime($data['created_at']) : null,
            updatedAt: isset($data['updated_at']) ? new DateTime($data['updated_at']) : null,
        );
    }

    public static function fromArray(array $data): self
    {
        return self::create($data);
    }

    public function getId(): int
    {
        return $this->boardId;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->boardId,
            'name'  => $this->name,
            'description' => $this->description,
            'columns' => $this->columns,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }
}
