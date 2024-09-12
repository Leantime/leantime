<?php

namespace Leantime\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class RemovePluginCommand
 *
 * This class represents a command that removes plugins.
 */
#[AsCommand(
    name: 'plugin:remove',
    description: 'Remove a plugin',
)]
class RemovePluginCommand extends AbstractPluginCommand
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

        if (! $this->confirm(sprintf('Remove plugin %s', $plugin->name))) {
            return Command::SUCCESS;
        }

        return $this->plugins->removePlugin($plugin->id) ? Command::SUCCESS : Command::FAILURE;
    }
}
