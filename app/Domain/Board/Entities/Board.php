<?php

namespace App\Domain\Board\Entities;

class Board
{
    public function __construct(
        public string $name,
        public ?int $boardId = null,
    ) {
    }


    public function getId(): int
    {
        return $this->boardId;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->boardId,
            'name'  => $this->name,
        ];
    }
}
