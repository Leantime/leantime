<?php

namespace Leantime\Command;

use Leantime\Domain\Plugins\Models\InstalledPlugin;
use Leantime\Domain\Plugins\Services\Plugins;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class AbstractPluginCommand
 */
abstract class AbstractPluginCommand extends Command
{
    protected InputInterface $input;
    protected SymfonyStyle $io;

    /**
     * Constructor.
     */
    public function __construct(
        protected readonly Plugins $plugins
    ) {
        parent::__construct();
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->io = new SymfonyStyle($input, $output);

        return $this->executeCommand();
    }

    /**
     * Execute the actual command.
     *
     * @return int
     */
    abstract protected function executeCommand(): int;

    /**
     * Asks a confirmation question.
     *
     * @param string $question
     * @return bool
     */
    protected function confirm(string $question): bool
    {
        return $this->io->confirm($question, !$this->input->isInteractive());
    }

    /**
     * @return array|InstalledPlugin[]
     */
    protected function getAllPlugins(): array
    {
        return array_values(
            array_merge(
                $this->plugins->getAllPlugins() ?: [],
                $this->plugins->discoverNewPlugins(),
            )
        );
    }

    /**
     * @return InstalledPlugin
     */
    protected function getPlugin(string $name): InstalledPlugin
    {
        foreach ($this->getAllPlugins() as $plugin) {
            if ($name === $plugin->name) {
                return $plugin;
            }
        }

        throw new RuntimeException(sprintf('Invalid plugin name: %s', $name));
    }
}
