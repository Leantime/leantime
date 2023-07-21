<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

// Don't run script unless using the 'run' command
if (! isset($_SERVER['argv'][1]) || $_SERVER['argv'][1] !== 'run') {
    return;
}

if (! file_exists($composer = __DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('Please run "make build-dev" to run tests.');
}

require $composer;

define('PROJECT_ROOT', realpath(__DIR__ . '/..') . '/');
define('APP_ROOT', PROJECT_ROOT . 'app/');
define('DEV_ROOT', PROJECT_ROOT . '.dev/');

$bootstrapper = get_class(new class {
    protected static $instance;
    protected $seleniumProcess;
    protected $dockerProcess;

    public static function getInstance()
    {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function start(): void
    {
        $this->startDevEnvironment();
        $this->createDatabase();
        $this->startSelenium();
        $this->createStep('Starting Codeception Testing Framework');
    }

    public function destroy(): void
    {
        $this->createStep('Stopping Codeception Testing Framework');
        $this->stopSelenium();
        $this->stopDevEnvironment();
    }

    public function stopSelenium(): void
    {
        $this->createStep('Stopping Selenium');

        try {
            $this->seleniumProcess->stop();
        // we want the script to continue even if failure
        } catch (Throwable $e) {
            return;
        }
    }

    public function stopDevEnvironment(): void
    {
        $this->createStep('Stopping Leantime Dev Environment');

        foreach (
            [
                fn () => $this->dockerProcess->stop(),
                fn () => $this->executeCommand('docker compose down', ['cwd' => DEV_ROOT]),
            ] as $count => $shutdown
        ) {
            try {
                $shutdown();
            // we want the script to continue even if failure
            } catch (Throwable $e) {
                if ($count === 1) {
                    return;
                }

                continue;
            }
        }
    }

    protected function startDevEnvironment(): void
    {
        $this->createStep('Build & Start Leantime Dev Environment');
        $this->dockerProcess = $this->executeCommand(
            [
                'docker',
                'compose',
                '-f',
                'docker-compose.yaml',
                '-f',
                'docker-compose.tests.yaml',
                'up',
                '-d',
                '--build',
                '--remove-orphans',
            ],
            [
                'cwd' => DEV_ROOT,
                'background' => true,
                'timeout' => 0,
            ]
        );
        $this->dockerProcess->waitUntil(function ($type, $buffer) {
            if (! isset($started)) {
                static $started = [
                    'dev-maildev-1' => false,
                    'dev-db-1' => false,
                    'dev-s3ninja-1' => false,
                    'dev-phpmyadmin-1' => false,
                    'dev-leantime-dev-1' => false,
                ];
            }

            foreach ($started as $container => $status) {
                if (! $status && strpos($buffer, "Container \"$container\" started") !== false) {
                    $started[$container] = true;
                }
            }

            $this->commandOutputHandler($type, $buffer);
            return ! in_array(false, $started, true);
        });
    }

    protected function createDatabase(): void
    {
        $this->createStep('Creating Test Database');
        $this->executeCommand(
            [
                'docker',
                'compose',
                'exec',
                '-T',
                'db',
                'mysql',
                '-uroot',
                '-pleantime',
                '-e',
                'DROP DATABASE IF EXISTS leantime_test;'
            ],
            ['cwd' => DEV_ROOT]
        );
        $this->executeCommand(
            [
                'docker',
                'compose',
                'exec',
                '-T',
                'db',
                'mysql',
                '-uroot',
                '-pleantime',
                '-e',
                'CREATE DATABASE IF NOT EXISTS leantime_test; GRANT ALL PRIVILEGES ON leantime_test.* TO \'leantime\'@\'%\'; FLUSH PRIVILEGES;',
            ],
            ['cwd' => DEV_ROOT]
        );
    }

    protected function startSelenium()
    {
        $this->createStep('Starting Selenium');
        $this->executeCommand(
            [
                'npx',
                'selenium-standalone',
                'install',
            ]
        );
        $this->seleniumProcess = $this->executeCommand([
            'npx',
            'selenium-standalone',
            'start',
        ], ['background' => true]);
        $this->seleniumProcess->waitUntil(function ($type, $buffer) {
            $this->commandOutputHandler($type, $buffer);
            return strpos($buffer, 'Selenium started') !== false;
        });
    }

    protected function createStep(string $message): void
    {
        $chars = strlen($message);
        $line = str_repeat('=', $chars);

        echo "\n$line\n$message\n$line\n";
    }

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

    private function commandOutputHandler($type, $buffer): void
    {
        echo Process::ERR === $type
            ? "\nSTDERR: $buffer"
            : "\nSTDOUT: $buffer";
    }
});

register_shutdown_function(fn () => $bootstrapper::getInstance()->destroy());
$bootstrapper::getInstance()->start();
