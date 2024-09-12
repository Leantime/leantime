<?php

namespace Leantime\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class InstallPluginCommand
 *
 * This class represents a command that installs plugins.
 */
#[AsCommand(
    name: 'plugin:install',
    description: 'Install a plugin',
)]
class InstallPluginCommand extends AbstractPluginCommand
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

        if (! isset($plugin->foldername)) {
            throw new RuntimeException(sprintf('Plugin %s cannot be installed', $plugin->name));
        }

        if (! $this->confirm(sprintf('Install plugin %s', $plugin->name))) {
            return Command::SUCCESS;
        }

        return $this->plugins->installPlugin($plugin->foldername) ? Command::SUCCESS : Command::FAILURE;
    }
}
