<?php

namespace Leantime\Domain\Oidc\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the OIDC settings before Leantime uses them.
 *
 * The payload holds every oidc* setting as the environment defines it (LEAN_OIDC_*), keyed by
 * its config name ('oidcEnable', 'oidcClientId', ...). Listeners return it with their changes;
 * AdvancedAuth uses this to apply the OIDC provider an admin entered in Authentication Settings.
 *
 * Settings that only the server operator may change (TLS verification, certificate files) are
 * restored from the environment after the filter runs, whatever a listener returns. See
 * {@see \Leantime\Domain\Oidc\Services\OidcConfig::ENV_ONLY_KEYS}.
 *
 *     EventDispatcher::add_filter_listener(OidcConfigFilter::class,
 *         fn (array $config, OidcConfigFilter $filter) => $config);
 */
final class OidcConfigFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array<string, mixed>  $config  The oidc* settings from the environment.
     */
    public function __construct(
        public array $config,
    ) {}

    /**
     * The OIDC settings threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->config;
    }
}
