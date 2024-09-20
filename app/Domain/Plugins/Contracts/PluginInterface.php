<?php

namespace Leantime\Domain\Plugins\Contracts;

/**
 * Interface PluginInterface.
 *
 * This interface represents a plugin that can be installed, uninstalled, enabled, and disabled.
 */
interface PluginInterface
{
    /**
     * Installs the plugin.
     *
     * @return bool True if the installation is successful, false otherwise.
     */
    public function install(): bool;

    /**
     * Uninstalls the plugin.
     *
     * This method performs the necessary actions to uninstall the application and remove all associated files and data.
     *
     * @return bool Returns true if the uninstallation is successful, false otherwise.
     */
    public function uninstall(): bool;

    /**
     * Enables the plugin.
     *
     * This method performs the necessary actions to enable the specified functionality. It may update configuration settings, start background processes, or perform any other actions required
     * to enable the functionality.
     *
     * @return bool Returns true if the enable operation is successful, false otherwise.
     */
    public function enable(): bool;

    /**
     * Disable the plugin.
     *
     * This method disables the functionality and returns a boolean value indicating whether the functionality is successfully disabled or not.
     *
     * @return bool True if the functionality is successfully disabled, false otherwise.
     */
    public function disable(): bool;
}
