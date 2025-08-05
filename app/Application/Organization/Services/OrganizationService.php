<?php

namespace App\Application\Organization\Services;

use App\Domain\Organization\Entities\OrganizationInvitation;
use App\Domain\Organization\Enums\InvitationStatus;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Repositories\OrgInvitationRepositoryInterface;
use App\Domain\Organization\Services\OrganizationDomainService;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation as ModelsOrganizationInvitation;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(MissingImport)
 */
class OrganizationService
{
    public function __construct(
        private OrganizationDomainService $orgDomainService,
        private OrganizationRepositoryInterface $orgRepository,
        private OrgInvitationRepositoryInterface $invitationRepository,
        private UserRepositoryInterface $userRepository,
        private Connection $database,
        private Str $str,
        private LoggerInterface $logger,
        LogManager $logManager,
    ) {
        $this->logger = $logManager->channel('organization');
    }

    public function create(User $user, array $data): Organization
    {
        $this->orgDomainService->validateOrganizationName($data['name']);

        return $this->database->transaction(function () use ($user, $data) {
            $organization = $this->orgRepository->save([
                'name' => $data['name'],
                'owner_id' => $user->id
            ]);

            $organization->users()->attach($user->id, ['role' => 'admin']);

            $user->current_organization_id = $organization->id;
            $user->save();

            return $organization;
        });
    }

    public function createInvitation(
        int $organizationId,
        string $email,
        string $role = 'member'
    ): ModelsOrganizationInvitation {
        try {
            $invitationEntity = new OrganizationInvitation(
                email: $email,
                token: $this->str->uuid()->toString(),
                organizationId: $organizationId,
                role: $role,
                status: InvitationStatus::PENDING,
            );

            $invitation = $this->invitationRepository->create($invitationEntity);

            $this->sendMail($invitation, $email, $organizationId);

            return $invitation;
        } catch (\Exception $e) {
            $this->logger->error("Failed to create and send organization invitation", [
                'organization_id' => $organizationId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Failed to create organization invitation: " . $e->getMessage());
        }
    }

    public function acceptInvitation(string $token, User $user): void
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (!$invitation) {
            throw new \RuntimeException("Invalid invitation token");
        }

        if (strcasecmp($invitation->email, $user->email) !== 0) {
            throw new \RuntimeException("This invitation does not belong to your account.");
        }

        $user = $this->userRepository->findByEmail($user->email);

        if (!$user) {
            throw new \RuntimeException("User not found");
        }

        $user->joinOrganization($invitation->organization_id, $invitation->role);

        $this->invitationRepository->updateStatus($token, InvitationStatus::ACCEPTED->value);
    }

    private function sendMail(ModelsOrganizationInvitation $invitation, string $email, int $organizationId): void
    {
        try {
            Mail::to($email)->send(new OrganizationInvitationMail($invitation));

            $this->logger->info("Invitation email sent successfully", [
                'organization_id' => $organizationId,
                'email' => $email,
                'invitation_id' => $invitation->id ?? null,
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Failed to send invitation email", [
                'organization_id' => $organizationId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException("Failed to send invitation email: " . $e->getMessage());
        }
    }
}
