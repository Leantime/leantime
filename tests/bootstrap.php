<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

// Don't run the script unless using the 'run' command
if (!isset($_SERVER['argv'][1]) || $_SERVER['argv'][1] !== 'run') {
    return;
}

if (!file_exists($composer = __DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('Please run "make build-dev" to run tests.');
}

require $composer;

define('PROJECT_ROOT', realpath(__DIR__ . '/..') . '/');
define('APP_ROOT', PROJECT_ROOT . '/');
define('DEV_ROOT', PROJECT_ROOT . '.dev/');

$bootstrapper = get_class(new class {
    /**
     * @var self
     */
    protected static $instance;

    /**
     * Get the singleton instance of this class
     *
     * @access public
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Start the testing environment
     *
     * @access public
     *
     * @return void
     */
    public function start(): void
    {
        $this->setFolderPermissions();
        $this->createDatabase();
        $this->createStep('Starting Codeception Testing Framework');
    }

    /**
     * Destroy the testing environment
     *
     * @access public
     *
     * @return void
     */
    public function destroy(): void
    {
        $this->createStep('Stopping Codeception Testing Framework');
    }

    /**
     * Create the test database
     *
     * @access protected
     *
     * @return void
     */
    protected function createDatabase(): void
    {
        $this->createStep('Dropping Test Database');
        $this->executeCommand(
            [
                'mysql',
                '--host=db',
                '--user=root',
                '--password=leantime',
                '-e',
                'DROP DATABASE IF EXISTS leantime_test;',
            ],
            ['cwd' => DEV_ROOT]
        );

        $this->createStep('Creating Test Database');
        $this->executeCommand(
            [
                'mysql',
                '--host=db',
                '--user=root',
                '--password=leantime',
                '-e',
                'CREATE DATABASE IF NOT EXISTS leantime_test; GRANT ALL PRIVILEGES ON leantime_test.* TO \'leantime\'@\'%\'; FLUSH PRIVILEGES;',
            ],
            ['cwd' => DEV_ROOT]
        );
    }

    protected function setFolderPermissions(): void
    {
        $this->createStep('Setting folder permissions on cache folder');

        //Set file permissions
        $this->executeCommand(
            array_filter(
                [
                    'chown',
                    '-R',
                    'www-data:www-data',
                    '/var/www/html/cache/',
                ]
            ),
            [
                'cwd' => DEV_ROOT,
            ]
        );

        $this->executeCommand(
            array_filter(
                [
                    'chown',
                    '-R',
                    'www-data:www-data',
                    '/var/www/html/logs/',
                ]
            ),
            [
                'cwd' => DEV_ROOT,
            ]
        );
    }

    /**
     * Create a step in the output
     *
     * @access protected
     * @param  string $message
     * @return void
     */
    protected function createStep(string $message): void
    {
        $chars = strlen($message);
        $line = str_repeat('=', $chars);

        echo "\n$line\n$message\n$line\n";
    }

    /**
     * Execute a command
     *
     * @access protected
     * @param  string|array $command
     * @param  array        $args
     * @param  boolean      $required
     * @return Process|string
     */
    protected function executeCommand(
        string|array $command,
        array $args = [],
        bool $required = true,
    ): Process|string {
        $process = is_array($command)
            ? new Process($command)
            : Process::fromShellCommandline($command);

        if (isset($args['cwd'])) {
            $process->setWorkingDirectory($args['cwd']);
        }

        if (isset($args['timeout'])) {
            $process->setTimeout($args['timeout']);
        }

        if (isset($args['options'])) {
            $process->setOptions($args['options']);
        }

        if (isset($args['background']) && $args['background']) {
            $process->start();
        } else {
            $process->run(fn ($type, $buffer) => $this->commandOutputHandler($type, $buffer));
        }

        if (
            $required
            && (! isset($args['background']) || ! $args['background'])
            && ! $process->isSuccessful()
        ) {
            throw new ProcessFailedException($process);
        }

        if (
            isset($args['getOutput'])
            && $args['getOutput']
        ) {
            if (isset($args['background']) && $args['background']) {
                throw new RuntimeException('Cannot get output from background process');
            }

            return $process->getOutput();
        }

        return $process;
    }

    /**
     * Handle command output
     *
     * @access private
     *
     * @param  string $type
     * @param  string $buffer
     *
     * @return void
     */
    private function commandOutputHandler(string $type, string $buffer): void
    {
        echo Process::ERR === $type ? "\nSTDERR: $buffer" : "\nSTDOUT: $buffer";
    }
});

register_shutdown_function(fn () => $bootstrapper::getInstance()->destroy());

$bootstrapper::getInstance()->start();
