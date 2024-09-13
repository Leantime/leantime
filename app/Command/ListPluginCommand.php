<?php

namespace Leantime\Command;

use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Symfony\Component\Console\Attribute\AsCommand;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

use function Symfony\Component\String\u;

/**
 * Class ListPluginCommand
 *
 * This class represents a command that lists all plugins.
 */
#[AsCommand(
    name: 'plugin:list',
    description: 'List all plugins',
)]
final class ListPluginCommand extends AbstractPluginCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addOption('order-by', null, InputOption::VALUE_REQUIRED, 'Plugin order', 'name');
        $this->addOption('installed', null, InputOption::VALUE_REQUIRED, 'Filter plugins on installed status');
        $this->addOption('enabled', null, InputOption::VALUE_REQUIRED, 'Filter plugins on enabled status');
        $this->setHelp(<<<'EOL'
Examples

Show all plugins:

    bin/leantime plugin:list

Show only installed plugins:

    bin/leantime plugin:list --installed=true

Show only non-installed plugins:

    bin/leantime plugin:list --installed=false

Show only enabled plugins:

    bin/leantime plugin:list --enabled=true

Show plugins that are both installed and enabled:

    bin/leantime plugin:list --installed=true --enabled=true

EOL);
    }

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(): int
    {
        $plugins = $this->getAllPlugins();

        // Filter by “installed" if requested.
        $installed = $this->input->getOption('installed');
        if ($installed !== null) {
            $installed = $installed === null || filter_var($installed, FILTER_VALIDATE_BOOLEAN);
            $plugins = array_filter($plugins, fn (InstalledPlugin $p) => ! ($installed xor isset($p->id)));
        }

        // Filter by “enabled" if requested.
        $enabled = $this->input->getOption('enabled');
        if ($enabled !== null) {
            $enabled = $enabled === null || filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
            $plugins = array_filter($plugins, fn (InstalledPlugin $p) => ! ($enabled xor $p->enabled));
        }

        $orderBy = $this->input->getOption('order-by');
        usort($plugins, static fn (InstalledPlugin $p0, InstalledPlugin $p1) => ($p0->{$orderBy} ?? null) <=> ($p1->{$orderBy} ?? null));

        foreach ($plugins as $plugin) {
            $this->io->definitionList(
                // Pad name to line up with wrapped description.
                ['Name' => u($plugin->name)->padEnd(60)],
                ['Description' => u($plugin->description)->wordwrap(60)],
                ['Installed' => isset($plugin->id) ? 'yes' : 'no'],
                ['Enabled' => $plugin->enabled ? 'yes' : 'no'],
            );
        }

        return Command::SUCCESS;
    }
}
