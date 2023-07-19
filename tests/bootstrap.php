<?php

declare(strict_types=1);

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

if (! file_exists($composer = __DIR__ . '/../vendor/autoload.php')) {
    throw new RuntimeException('Please run "make build-dev" to run tests.');
}

require $composer;

define('PROJECT_ROOT', realpath(__DIR__ . '/..') . '/');
define('APP_ROOT', PROJECT_ROOT . 'app/');
define('DEV_ROOT', PROJECT_ROOT . '.dev/');

new class
{
    public function __construct()
    {
        $this->startDevEnvironment();
        $this->createDatabase();
        $this->startSelenium();
        $this->createStep('Starting Codeception Testing Framework');
    }

    protected function startDevEnvironment(): void
    {
        $this->createStep('Build & Start Leantime Dev Environment');
        $this->executeCommand(
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
                'timeout' => 0,
            ]
        );
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
        $this->executeCommand('npx selenium-standalone start > /dev/null 2>&1 &');
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
    ): void {
        $process = is_array($command)
            ? new Process($command)
            : Process::fromShellCommandline($command);

        if (isset($args['cwd'])) {
            $process->setWorkingDirectory($args['cwd']);
        }

        if (isset($args['timeout'])) {
            $process->setTimeout($args['timeout']);
        }

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo "\nSTDERR: $buffer";
            } else {
                echo "\nSTDOUT: $buffer";
            }
        });

        if ($required && ! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
};
