<?php

namespace App\Providers;

use App\Domain\Board\Repositories\BoardRepositoryInterface;
use App\Domain\Column\Repositories\ColumnRepositoryInterface;
use App\Domain\Organization\Repositories\OrganizationRepositoryInterface;
use App\Domain\Organization\Repositories\OrgInvitationRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\BoardRepository;
use App\Infrastructure\Persistence\ColumnRepository;
use App\Infrastructure\Persistence\OrganizationInvitationRepository;
use App\Infrastructure\Persistence\OrganizationRepository;
use App\Infrastructure\Persistence\UserRepository;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
