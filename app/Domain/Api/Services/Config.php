<?php

namespace Leantime\Domain\Api\Services;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Plugins\Services\Plugins as PluginsService;

/**
 * Core capability-discovery service for JSON-RPC clients (mobile, MCP, web).
 *
 * Always available regardless of which plugins are installed — paired with the
 * RequiresPlugin attribute so clients can gate UI client-side instead of discovering
 * disabled capabilities through failed RPC calls.
 *
 * Capability staleness: client-side cache should refresh on next login. Admin toggles
 * propagate on next session; up-to-session-length staleness is acceptable.
 *
 * @api
 */
class Config
{
    public function __construct(
        private AppSettings $appSettings,
        private PluginsService $pluginsService,
    ) {}

    /**
     * Return system version + enabled-plugin list for capability discovery.
     *
     * @return array{version: string, enabledPlugins: array<int, string>}
     *
     * @api
     */
    public function getSystemInfo(): array
    {
        $enabled = $this->pluginsService->getEnabledPlugins() ?: [];

        $pluginFolders = [];
        foreach ($enabled as $plugin) {
            $folder = is_object($plugin) ? ($plugin->foldername ?? null) : ($plugin['foldername'] ?? null);
            if ($folder) {
                $pluginFolders[] = $folder;
            }
        }

        return [
            'version' => $this->appSettings->appVersion,
            'enabledPlugins' => $pluginFolders,
        ];
    }
}
