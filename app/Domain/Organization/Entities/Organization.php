<?php

namespace App\Domain\Organization\Entities;

class Organization
{
    public function __construct(
        public string $name,
        public ?int $organizationId = null,
    ) {
    }


    public function getId(): int
    {
        return $this->organizationId;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function toArray(): array
    {
        return [
            'id'    => $this->organizationId,
            'name'  => $this->name,
        ];
    }
}
