<?php

namespace App\Domain\Organization\Entities;

use App\Domain\Organization\Enums\InvitationStatus;

class OrganizationInvitation
{
    public function __construct(
        public string $email,
        public string $token,
        public int $organizationId,
        public string $role = 'member',
        public InvitationStatus $status = InvitationStatus::PENDING
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getToken(): string
    {
        return $this->token;
    }
    
    public function getOrganizationId(): int
    {
        return $this->organizationId;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getStatus(): InvitationStatus
    {
        return $this->status;
    }
    
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'token' => $this->token,
            'organization_id' => $this->organizationId,
            'role' => $this->role,
            'status' => $this->status->value,
        ];
    }
}
