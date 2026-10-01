<?php

declare(strict_types=1);

namespace Leantime\Core\Resources;

use Illuminate\Support\ServiceProvider;
use Leantime\Core\Resources\Services\ResourcesRegistry;

/**
 * Registers the Resources contract surface.
 *
 * Resources are a plugin-provided data category (people allocations, budget
 * lines, dependencies at the program level). Core owns the contract and the
 * registry; a plugin (currently PgmPro) registers itself as *the* provider
 * from its own boot. Nothing seeded here — an install with no Resources
 * plugin gets a registry that resolves to null, which every consumer
 * handles as the honest "no provider" state.
 *
 * See {@see \Leantime\Core\Resources\Contracts\ResourcesGateway}.
 */
class ResourcesServiceProvider extends ServiceProvider
{
    /**
     * Bind the registry as a singleton — a single provider registration must
     * be visible to every consumer for the life of the request.
     */
    public function register(): void
    {
        $this->app->singleton(ResourcesRegistry::class);
    }
}
