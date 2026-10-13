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
 */
class PluginCompatibility
{
    /** Range assumed for plugins that declare none (everything released before 3.12 targets 3.x). */
    public const UNDECLARED_RANGE = '>=3.0 <4.0';

    /** @var array<string, bool> Per-request results of {@see pluginAtPathIsCompatible()}, by path and version. */
    private static array $pathResults = [];

    public function __construct(private AppSettings $appSettings) {}

    /**
     * Whether the folder plugin at $pluginPath supports the running Leantime version.
     *
     * For system plugins (from config), which core loads straight from their folder rather than
     * through the enabled-plugin list. A plugin without a readable composer.json declares no
     * range, so it gets {@see UNDECLARED_RANGE} like any other plugin that declares none.
     *
     * @param  string  $pluginPath  The plugin's folder, e.g. APP_ROOT.'/app/Plugins/Notes'.
     */
    public function pluginAtPathIsCompatible(string $pluginPath): bool
    {
        $pluginPath = rtrim($pluginPath, '/');
        $resultKey = $pluginPath.'|'.$this->appSettings->appVersion;
        if (isset(self::$pathResults[$resultKey])) {
            return self::$pathResults[$resultKey];
        }

        $composer = is_file($pluginPath.'/composer.json')
            ? json_decode((string) file_get_contents($pluginPath.'/composer.json'), true)
            : null;

        $range = $this->rangeFor(is_array($composer) ? $composer : []);
        $compatible = $this->isCompatible($range);

        if (! $compatible) {
            Log::warning('Not loading system plugin '.basename($pluginPath).': it supports Leantime '.$range.', this is '.$this->appSettings->appVersion);
        }

        return self::$pathResults[$resultKey] = $compatible;
    }

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
