<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Resources\Contracts\ResourcesGateway;

/**
 * Registers the single Resources provider for this installation.
 *
 * The rest of the app (ReportEngine, StrategyReport, any UI wanting a
 * Resources section) asks this registry for a gateway and gets either the
 * one implementation a plugin registered, or null. Null is the honest
 * "no plugin installed" state — callers branch on it.
 *
 * v1 assumes a single provider (PgmPro). If a second plugin ever registers
 * a gateway on the same install, the first registration wins and the second
 * is logged. That's a conservative default — silently letting a second
 * plugin overwrite the first would cause a data-source ambiguity nobody
 * would notice until reports started disagreeing.
 */
class ResourcesRegistry
{
    private ?ResourcesGateway $gateway = null;

    private ?string $registeredBy = null;

    /**
     * Called by the providing plugin's register.php on boot. Same-class
     * re-registration replaces the stored instance (last write wins; safe
     * because two instances of the same gateway are interchangeable).
     * Cross-class registration is refused and logged so double-installs
     * don't silently fight.
     *
     * @param  ResourcesGateway  $gateway  The plugin's implementation.
     */
    public function register(ResourcesGateway $gateway): void
    {
        $incoming = $gateway::class;

        if ($this->gateway !== null && $this->registeredBy !== $incoming) {
            Log::warning(sprintf(
                'ResourcesRegistry: %s tried to register a Resources gateway, but %s is already registered. Keeping the first registration.',
                $incoming,
                $this->registeredBy,
            ));

            return;
        }

        $this->gateway = $gateway;
        $this->registeredBy = $incoming;
    }

    /**
     * Returns the registered gateway, or null when no plugin has registered
     * one. This is the "no Resources provider installed" state — every
     * consumer must handle it.
     */
    public function resolve(): ?ResourcesGateway
    {
        return $this->gateway;
    }

    /**
     * True when a provider is registered. Convenience for template-level
     * checks that don't want to hold a gateway reference.
     */
    public function hasProvider(): bool
    {
        return $this->gateway !== null;
    }
}
