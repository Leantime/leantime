<?php

namespace Leantime\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Class EnablePluginCommand
 *
 * This class represents a command that enables plugins.
 */
#[AsCommand(
    name: 'plugin:enable',
    description: 'Enable a plugin',
)]
class EnablePluginCommand extends AbstractPluginCommand
{
    /**
     * {@inheritdoc}
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('plugin', InputArgument::REQUIRED, 'The plugin name');
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    protected function executeCommand(): int
    {
        $name = $this->input->getArgument('plugin');
        $plugin = $this->getPlugin($name);

        if (!isset($plugin->id)) {
            throw new RuntimeException(sprintf('Plugin %s is not installed', $plugin->name));
        }

        if ($plugin->enabled) {
            throw new RuntimeException(sprintf('Plugin %s is already enabled', $plugin->name));
        }

        if (!$this->confirm(sprintf('Enable plugin %s', $plugin->name))) {
            return Command::SUCCESS;
        }

        return $this->plugins->enablePlugin($plugin->id) ? Command::SUCCESS : Command::FAILURE;
    }
}
