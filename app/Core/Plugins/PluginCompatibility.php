<?php

namespace Leantime\Core\Plugins;

use Composer\Semver\Semver;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\AppSettings;

/**
 * Decides whether a plugin can run on a Leantime version.
 *
 * A plugin declares the Leantime versions it supports in its composer.json, as a Composer
 * version constraint:
 *
 *     "extra": { "leantime": { "requires": ">=3.12 <4.0" } }
 *
 * Plugins released before this existed declare nothing. They were all built for Leantime 3.x,
 * so they are treated as {@see UNDECLARED_RANGE}: they keep loading on every 3.x release and
 * are flagged as incompatible on 4.0.
 *
 * System plugins (LEAN_PLUGINS) are not guarded: they come from the deployment's config, and
 * whoever configures them is responsible for running versions that fit.
 */
class PluginCompatibility
{
    /** Range assumed for plugins that declare none (everything released before 3.12 targets 3.x). */
    public const UNDECLARED_RANGE = '>=3.0 <4.0';

    public function __construct(private AppSettings $appSettings) {}

    /**
     * The range a plugin declares in its composer.json, or null when it declares none.
     *
     * @param  array<string, mixed>  $composer  The plugin's decoded composer.json.
     */
    public function declaredRange(array $composer): ?string
    {
        $range = $composer['extra']['leantime']['requires'] ?? null;

        return is_string($range) && trim($range) !== '' ? trim($range) : null;
    }

    /**
     * The range a plugin supports: its declared range, or {@see UNDECLARED_RANGE}.
     *
     * @param  array<string, mixed>  $composer  The plugin's decoded composer.json.
     */
    public function rangeFor(array $composer): string
    {
        return $this->declaredRange($composer) ?? self::UNDECLARED_RANGE;
    }

    /**
     * Whether a Leantime version satisfies a plugin's range.
     *
     * An unparseable range counts as incompatible: a plugin whose requirement can't be read
     * must not be loaded on the assumption that it works.
     *
     * @param  string  $range  A Composer version constraint, e.g. ">=3.12 <4.0" or "^3.12".
     * @param  string|null  $leantimeVersion  The Leantime version to check; default the running one.
     */
    public function isCompatible(string $range, ?string $leantimeVersion = null): bool
    {
        $leantimeVersion = ltrim($leantimeVersion ?? $this->appSettings->appVersion, 'v');

        try {
            return Semver::satisfies($leantimeVersion, $range);
        } catch (\UnexpectedValueException $e) {
            Log::warning('Unreadable plugin version range "'.$range.'": '.$e->getMessage());

            return false;
        }
    }
}
