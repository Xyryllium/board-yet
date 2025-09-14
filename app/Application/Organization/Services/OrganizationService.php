<?php

namespace App\Application\Organization\Services;

use Exception;
use RuntimeException;
use App\Domain\Organization\Entities\OrganizationInvitation;
use App\Domain\Organization\Enums\InvitationStatus;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Repositories\OrgInvitationRepositoryInterface;
use App\Domain\Organization\Services\OrganizationDomainService;
use App\Domain\User\Exceptions\UserNotRegisteredException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Events\OrganizationInvitationSent;
use App\Models\Organization;
use App\Models\OrganizationInvitation as ModelsOrganizationInvitation;
use App\Models\User;
use Illuminate\Database\Connection;
use Illuminate\Log\LogManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

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

            $this->logger->info("Dispatching OrganizationInvitationSent event", [
                'invitation_id' => $invitation->id,
                'email' => $invitation->email,
            ]);

            Event::dispatch(new OrganizationInvitationSent($invitation));

            return $invitation;
        } catch (Exception $e) {
            $this->logger->error("Failed to create organization invitation", [
                'organization_id' => $organizationId,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException("Failed to create organization invitation: " . $e->getMessage());
        }
    }

    public function acceptInvitation(string $token, ?User $user): void
    {
        $invitation = $this->invitationRepository->findByToken($token);

        if (!$invitation) {
            throw new RuntimeException("Invalid invitation token");
        }

        $user = $user ? $this->userRepository->findByEmail($user->email) : null;

        if (!$user) {
            throw new UserNotRegisteredException($invitation->email, $token);
        }

        if (strcasecmp($invitation->email, $user->email) !== 0) {
            throw new RuntimeException("This invitation does not belong to your account.");
        }

        $user->joinOrganization($invitation->organization_id, $invitation->role);

        $this->invitationRepository->updateStatus($token, InvitationStatus::ACCEPTED->value);
    }
}
