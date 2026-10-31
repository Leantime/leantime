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
 *
 * A released plugin can't change its declared range. When core removes something an already
 * released version uses, that version goes into {@see BROKEN_RELEASES}, which caps its range.
 */
class PluginCompatibility
{
    /** Range assumed for plugins that declare none (everything released before 3.12 targets 3.x). */
    public const UNDECLARED_RANGE = '>=3.0 <4.0';

    /**
     * Released plugin versions that declare a range including Leantime versions they can't run on,
     * because core removed something they use after they shipped. Their range is capped at the
     * last Leantime version that still had it, so the guard, My Apps, the admin notice and the
     * update check treat them like any incompatible plugin and offer the fixed release.
     *
     * Lowercase package name => [affected plugin versions, last Leantime version they run on].
     */
    public const BROKEN_RELEASES = [
        // They call <x-global::accordion>, removed after 3.13.2. 1.6.0 uses elements.accordion.
        'leantime/customfields' => ['<1.6.0', '3.13.2'],
    ];

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
     * The range a plugin supports: its declared range, or {@see UNDECLARED_RANGE}, capped for a
     * release listed in {@see BROKEN_RELEASES}.
     *
     * @param  array<string, mixed>  $composer  The plugin's decoded composer.json.
     */
    public function rangeFor(array $composer): string
    {
        $range = $this->declaredRange($composer) ?? self::UNDECLARED_RANGE;

        return $this->capBrokenRelease($range, (string) ($composer['name'] ?? ''), (string) ($composer['version'] ?? ''));
    }

    /**
     * A release's range, capped at the last Leantime version it runs on when the release is listed
     * in {@see BROKEN_RELEASES}. Used for installed plugins ({@see rangeFor()}) and for marketplace
     * releases offered as updates.
     *
     * @param  string  $range  The release's own range.
     * @param  string  $packageName  The plugin's composer package name, e.g. "leantime/customFields".
     * @param  string  $pluginVersion  The release's version.
     */
    public function capBrokenRelease(string $range, string $packageName, string $pluginVersion): string
    {
        $lastLeantimeVersion = $this->lastLeantimeVersionForBrokenRelease($packageName, $pluginVersion);
        if ($lastLeantimeVersion === null) {
            return $range;
        }

        // The cap must hold for every alternative of an "a || b" range: AND binds tighter than OR.
        $alternatives = preg_split('/\s*\|\|?\s*/', $range);

        return implode(' || ', array_map(fn (string $alternative) => $alternative.' <='.$lastLeantimeVersion, $alternatives));
    }

    /**
     * The last Leantime version a plugin release runs on when it is listed in
     * {@see BROKEN_RELEASES}, or null when it isn't.
     */
    private function lastLeantimeVersionForBrokenRelease(string $packageName, string $pluginVersion): ?string
    {
        $packageName = strtolower($packageName);
        $pluginVersion = ltrim($pluginVersion, 'v');

        if (! isset(self::BROKEN_RELEASES[$packageName]) || $pluginVersion === '') {
            return null;
        }

        [$affectedVersions, $lastLeantimeVersion] = self::BROKEN_RELEASES[$packageName];

        try {
            return Semver::satisfies($pluginVersion, $affectedVersions) ? $lastLeantimeVersion : null;
        } catch (\UnexpectedValueException $e) {
            return null;
        }
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
