<?php

namespace App\Providers;

use App\Application\Auth\Services\AuthService;
use App\Application\Auth\Services\SessionService;
use App\Domain\Auth\Repositories\AuthRepositoryInterface;
use App\Domain\Auth\Services\AuthDomainService;
use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Domain\Card\Repositories\CardRepositoryInterface;
use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Repositories\OrgInvitationRepositoryInterface;
use App\Domain\PasswordReset\Repositories\PasswordResetRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\UserRoleDomainService;
use App\Infrastructure\Persistence\AuthRepository;
use App\Infrastructure\Persistence\BoardRepository;
use App\Infrastructure\Persistence\CardRepository;
use App\Infrastructure\Persistence\ColumnRepository;
use App\Infrastructure\Persistence\OrganizationInvitationRepository;
use App\Infrastructure\Persistence\OrganizationRepository;
use App\Infrastructure\Persistence\PasswordResetRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Models\PersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(OrganizationRepositoryInterface::class, OrganizationRepository::class);
        $this->app->bind(OrgInvitationRepositoryInterface::class, OrganizationInvitationRepository::class);
        $this->app->bind(BoardRepositoryInterface::class, BoardRepository::class);
        $this->app->bind(ColumnRepositoryInterface::class, ColumnRepository::class);
        $this->app->bind(CardRepositoryInterface::class, CardRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(PasswordResetRepositoryInterface::class, PasswordResetRepository::class);

        $this->app->bind(SessionService::class, function ($app) {
            return new SessionService(
                $app->make(AuthRepositoryInterface::class)
            );
        });

        $this->app->bind(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(AuthDomainService::class),
                $app->make(SessionService::class),
            );
        });

        $this->app->bind(AuthDomainService::class, function ($app) {
            return new AuthDomainService(
                $app->make(AuthRepositoryInterface::class),
                $app->make(UserRepositoryInterface::class),
                $app->make(UserRoleDomainService::class),
                $app->make(OrganizationRepositoryInterface::class),
                $app->make('hash')
            );
        });

        $this->app->bind(UserRoleDomainService::class, function ($app) {
            return new UserRoleDomainService(
                $app->make(UserRepositoryInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
