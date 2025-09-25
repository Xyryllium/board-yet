<?php

namespace App\Domain\Organization\Entities;

use DateTime;

class Organization
{
    public function __construct(
        public ?int $organizationId,
        public string $name,
        public string $subdomain,
        public array $settings = [],
        public ?DateTime $createdAt = null,
        public ?DateTime $updatedAt = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            organizationId: $data['id'] ?? null,
            name: $data['name'],
            subdomain: $data['subdomain'] ?? null,
            settings: $data['settings'] ?? [],
            createdAt: $data['created_at'] ? new DateTime($data['created_at']) : null,
            updatedAt: $data['updated_at'] ? new DateTime($data['updated_at']) : null,
        );
    }

    public function getId(): int
    {
        return $this->organizationId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSubdomain(): string
    {
        return $this->subdomain;
    }

    public function getSettings(): array
    {
        return $this->settings;
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
            'id'        => $this->organizationId,
            'name'      => $this->name,
            'subdomain' => $this->subdomain,
            'settings'  => $this->settings,
        ];
    }
}
