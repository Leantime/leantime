<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Contracts\HasAbilities;
use Laravel\Sanctum\Sanctum as SanctumBase;
use Leantime\Domain\Auth\Services\AccessToken;

class Sanctum extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(HasAbilities::class, AccessToken::class);
    }

    public function boot(): void
    {
        SanctumBase::ignoreMigrations();

        // Use our custom token model
        SanctumBase::usePersonalAccessTokenModel(AccessToken::class);

    }
}
