<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class DisablePluginCommand
 *
 * This class represents a command that disables plugins.
 */
#[AsCommand(
    name: 'plugin:disable',
    description: 'Disable a plugin',
)]
class DisablePluginCommand extends AbstractPluginCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument('plugin', InputArgument::REQUIRED, 'The plugin name');
    }

    /**
     * {@inheritdoc}
     */
    protected function executeCommand(): int
    {
        $name = $this->input->getArgument('plugin');
        $plugin = $this->getPlugin($name);

        if (! isset($plugin->id)) {
            throw new RuntimeException(sprintf('Plugin %s is not installed', $plugin->name));
        }

        if (! $plugin->enabled) {
            throw new RuntimeException(sprintf('Plugin %s is not enabled', $plugin->name));
        }

        if (! $this->confirm(sprintf('Disable plugin %s', $plugin->name))) {
            return Command::SUCCESS;
        }

        return $this->plugins->disablePlugin($plugin->id) ? Command::SUCCESS : Command::FAILURE;
    }
}
