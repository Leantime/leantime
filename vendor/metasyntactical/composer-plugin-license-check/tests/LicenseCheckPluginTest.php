<?php

namespace Metasyntactical\Composer\LicenseCheck;

use Composer\InstalledVersions;
use Composer\Util\Filesystem;
use Composer\Util\Silencer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * @group integration
 */
final class LicenseCheckPluginTest extends TestCase
{
    private const COMPOSER_REQUIRES_SYMFONY_PROCESS_MIN = '2.1.0';

    private string $oldcwd;
    private ?string $oldenv = null;
    private ?string $testDir;
    private string $composerHomeDir;
    private string $composerExecutable;
    private string $projectDir;
    private bool $cmdAsArray;

    public function setUp(): void
    {
        $this->cmdAsArray = version_compare(InstalledVersions::getVersion('symfony/process') ?? self::COMPOSER_REQUIRES_SYMFONY_PROCESS_MIN, '3.3.0', 'ge');

        $this->oldcwd = getcwd();
        $this->testDir = self::getUniqueTmpDirectory();
        $this->composerHomeDir = $this->testDir . '/home';
        $this->composerExecutable = dirname(__DIR__) . '/vendor/bin/composer';
        $this->projectDir = $this->testDir . '/project';
        self::ensureDirectoryExistsAndClear($this->composerHomeDir);
        self::ensureDirectoryExistsAndClear($this->projectDir);
        file_put_contents($this->composerHomeDir . '/composer.json', '{"notify-on-install": false}');

        chdir($this->projectDir);
    }

    public function tearDown(): void
    {
        chdir($this->oldcwd);

        $fs = new Filesystem();

        if ($this->testDir) {
            $fs->removeDirectory($this->testDir);
            $this->testDir = null;
        }

        $this->resetComposerHome($fs);
    }

    private function resetComposerHome(Filesystem $fs): void
    {
        if ($this->oldenv) {
            $composerHome = getenv('COMPOSER_HOME');
            if (is_string($composerHome)) {
                $fs->removeDirectory($composerHome);
                $_SERVER['COMPOSER_HOME'] = $this->oldenv;
                putenv('COMPOSER_HOME=' . $_SERVER['COMPOSER_HOME']);
                $this->oldenv = null;
            }
        }
    }

    private function setComposerHome(): void
    {
        $oldenv = getenv('COMPOSER_HOME');
        if (!is_string($oldenv)) {
            return;
        }

        $this->oldenv = $oldenv;
        $_SERVER['COMPOSER_HOME'] = $this->composerHomeDir;
        putenv('COMPOSER_HOME=' . $_SERVER['COMPOSER_HOME']);
    }

    public function testLoadingOfPluginSucceeds(): void
    {
        $projectRoot = dirname(__DIR__);
        $this->writeComposerJson($projectRoot);

        $this->setComposerHome();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '-v',
            'install',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        $errorOutput = $this->cleanOutput($proc->getErrorOutput());

        self::assertStringContainsString('The Metasyntactical LicenseCheck Plugin has been enabled.', $errorOutput);
        self::assertSame(0, $exitcode);
    }

    public function testLicenseCheckCommand(): void
    {
        $projectRoot = dirname(__DIR__);
        $this->writeComposerJson(
            $projectRoot,
            [
                "metasyntactical/composer-plugin-license-check" => "dev-main@dev",
                "sebastian/version" => "2.0.1",
                "psr/log" => "1.1.0",
            ],
            [
                "metasyntactical/composer-plugin-license-check" => [
                    "allow-list" => [
                        "MIT",
                        "BSD-3-Clause",
                    ],
                    "deny-list" => [
                        "MIT",
                    ],
                ],
            ],
        );

        $this->setComposerHome();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '--no-plugins',
            '-v',
            'install',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertSame(0, $exitcode);

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            'check-licenses',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertStringContainsString('1.1.0     MIT           no', $this->cleanOutput($proc->getOutput()));
        self::assertStringContainsString('2.0.1     BSD-3-Clause  yes', $this->cleanOutput($proc->getOutput()));
        self::assertSame(1, $exitcode);
    }

    public function testLicenseCheckCommandWithAllowedPackage(): void
    {
        $projectRoot = dirname(__DIR__);
        $this->writeComposerJson(
            $projectRoot,
            [
                "metasyntactical/composer-plugin-license-check" => "dev-main@dev",
                "sebastian/version" => "2.0.1",
                "psr/log" => "1.1.0",
            ],
            [
                "metasyntactical/composer-plugin-license-check" => [
                    'allow-list' => [
                        'MIT',
                    ],
                    'allowed-packages' => [
                        'sebastian/version' => '*',
                    ],
                ],
            ],
        );

        $this->setComposerHome();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '--no-plugins',
            '-v',
            'install',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertSame(0, $exitcode);

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            'check-licenses',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertStringContainsString('1.1.0     MIT           yes', $this->cleanOutput($proc->getOutput()));
        self::assertStringContainsString('2.0.1     BSD-3-Clause  no (explicitly allowed)', $this->cleanOutput($proc->getOutput()));
        self::assertSame(0, $exitcode);
    }

    public function testRequiringPackageWithDisallowedLicenseFails(): void
    {
        $projectRoot = dirname(__DIR__);
        $this->writeComposerJson(
            $projectRoot,
            [
                "metasyntactical/composer-plugin-license-check" => "dev-main@dev",
                "psr/log" => "1.1.0",
            ],
            [
                "metasyntactical/composer-plugin-license-check" => [
                    'allow-list' => [
                        'MIT',
                    ],
                ],
            ],
        );

        $this->setComposerHome();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '--no-plugins',
            'install',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $proc->run();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            'require',
            'sebastian/version:^2.0',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertStringContainsString(
            'ERROR: Licenses "BSD-3-Clause" of package "sebastian/version" are not allow',
            $this->cleanOutput($proc->getErrorOutput())
        );
        self::assertSame(1, $exitcode);
    }

    public function testLicenseCheckSucceedsWithWarningIfPackageIsAllowed(): void
    {
        $projectRoot = dirname(__DIR__);
        $this->writeComposerJson(
            $projectRoot,
            [
                "metasyntactical/composer-plugin-license-check" => "dev-main@dev",
                "psr/log" => "1.1.0",
            ],
            [
                "metasyntactical/composer-plugin-license-check" => [
                    'allow-list' => [
                        'MIT',
                    ],
                    'allowed-packages' => [
                        'sebastian/version' => '*',
                    ],
                ],
            ],
        );

        $this->setComposerHome();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '--no-plugins',
            'install',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $proc->run();

        $cmd = [
            'php',
            $this->composerExecutable,
            '--no-ansi',
            '--no-progress',
            'require',
            'sebastian/version:^2.0',
        ];
        if (!$this->cmdAsArray) {
            $cmd = implode(' ', array_map([$this, 'escapeArgument'], $cmd));
        }
        $proc = new Process($cmd, $this->projectDir, null, null, 300);
        $exitcode = $proc->run();

        self::assertStringContainsString(
            'WARNING: Licenses "BSD-3-Clause" of package "sebastian/version" are not all',
            $this->cleanOutput($proc->getErrorOutput())
        );
        self::assertSame(0, $exitcode);
    }

    private static function getUniqueTmpDirectory(): string
    {
        $attempts = 5;
        $root = sys_get_temp_dir();

        do {
            try {
                $unique = $root . DIRECTORY_SEPARATOR . uniqid('composer-test-' . random_int(1000, 9000), false);
            } catch (Throwable $exception) {
                continue;
            }

            if (!file_exists($unique) && Silencer::call('mkdir', $unique, 0777)) {
                $path = realpath($unique);
                if ($path !== false) {
                    return $path;
                }
            }
        } while (--$attempts);

        throw new \RuntimeException('Failed to create a unique temporary directory.');
    }

    private static function ensureDirectoryExistsAndClear(string $directory): void
    {
        $fs = new Filesystem();

        if (is_dir($directory)) {
            $fs->removeDirectory($directory);
        }

        mkdir($directory, 0777, true);
    }

    private function cleanOutput(string $output): string
    {
        $processed = '';

        for ($i = 0, $maxLength = strlen($output); $i < $maxLength; $i++) {
            if ($output[$i] === "\x08") {
                $processed = substr($processed, 0, -1);
            } elseif ($output[$i] !== "\r") {
                $processed .= $output[$i];
            }
        }

        return $processed;
    }

    private function writeComposerJson(string $projectRoot, array $requires = null, array $extra = []): void
    {
        $projectRoot = str_replace('\\', '/', $projectRoot);
        if ($requires === null) {
            $requires = [
                "metasyntactical/composer-plugin-license-check" => "dev-main@dev",
            ];
        }
        $requiresJson = json_encode($requires, JSON_THROW_ON_ERROR);
        $extraJson = json_encode($extra, JSON_THROW_ON_ERROR);
        $composerJson = <<<_EOT
{
  "name": "metasyntactical/composer-plugin-license-check-test",
  "license": "MIT",
  "type": "project",
  "minimum-stability": "dev",
  "require": {$requiresJson},
  "extra": {$extraJson},
  "config": {
    "allow-plugins": {
      "metasyntactical/composer-plugin-license-check": true
    }
  },
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "metasyntactical/composer-plugin-license-check",
        "description": "Plugin for Composer to restrict installation of packages to valid licenses via allow-list.",
        "license": "MIT",
        "type": "composer-plugin",
        "require": {
          "php": "8.0.*|8.1.*|8.2.*",
          "composer-plugin-api": "^2.0"
        },
        "require-dev": {
          "composer/composer": "^2.0",
          "phpunit/phpunit": "^9.5"
        },
        "autoload": {
          "psr-4": {
            "Metasyntactical\\\\Composer\\\\LicenseCheck\\\\": "src/"
          }
        },
        "autoload-dev": {
          "psr-4": {
            "Metasyntactical\\\\Composer\\\\LicenseCheck\\\\": "tests/"
          }
        },
        "extra": {
          "class": "Metasyntactical\\\\Composer\\\\LicenseCheck\\\\LicenseCheckPlugin"
        },
        "version": "dev-main",
        "dist": {
          "url": "{$projectRoot}",
          "type": "path"
        }
      }
    }
  ]
}
_EOT;
        file_put_contents($this->projectDir . '/composer.json', $composerJson);
    }

    private function escapeArgument(string $argument): string
    {
        if ('\\' !== DIRECTORY_SEPARATOR) {
            return "'" . str_replace("'", "'\\''", $argument) . "'";
        }
        if ('' === $argument) {
            return '""';
        }
        if (false !== strpos($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"' . str_replace(array('"', '^', '%', '!', "\n"), array('""', '"^^"', '"^%"', '"^!"', '!LF!'), $argument) . '"';
    }
}
