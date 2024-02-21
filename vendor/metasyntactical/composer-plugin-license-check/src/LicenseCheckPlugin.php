<?php

declare(strict_types=1);

namespace Metasyntactical\Composer\LicenseCheck;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Package\CompletePackageInterface;
use Composer\Package\PackageInterface;
use Composer\Package\RootPackageInterface;
use Composer\Plugin\Capability\Capability as CapabilityInterface;
use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Composer\Plugin\Capable as CapableInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Metasyntactical\Composer\LicenseCheck\Command\CommandProvider;

final class LicenseCheckPlugin implements PluginInterface, CapableInterface, EventSubscriberInterface
{
    public const PLUGIN_PACKAGE_NAME = 'metasyntactical/composer-plugin-license-check';

    private Composer $composer;

    private IOInterface $io;

    private array $allowedLicenses = [];

    private array $deniedLicenses = [];

    private array $allowedPackages = [];

    public function __construct()
    {
        $this->io = new NullIO();
        $this->composer = new Composer();
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;

        $rootPackage = $composer->getPackage();

        $config = $this->getConfig($rootPackage);

        $this->allowedLicenses = $config->allowList();
        $this->deniedLicenses = $config->denyList();
        $this->allowedPackages = $config->allowePackages();
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    /**
     * @psalm-return array<class-string, class-string>
     */
    public function getCapabilities(): array
    {
        return [
            CommandProviderCapability::class => CommandProvider::class
        ];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PluginEvents::COMMAND => [['handleEventAndOutputDebugMessage', 101]],
            PackageEvents::POST_PACKAGE_INSTALL => [['handleEventAndCheckLicense', 100]],
            PackageEvents::POST_PACKAGE_UPDATE => [['handleEventAndCheckLicense', 100]],
        ];
    }

    public function handleEventAndOutputDebugMessage(CommandEvent $event): void
    {
        if (!in_array($event->getCommandName(), ['install', 'update'], true)) {
            return;
        }
        if (!$this->io->isVerbose()) {
            return;
        }

        $this->io->writeError('<info>The Metasyntactical LicenseCheck Plugin has been enabled.</info>');
    }

    public function handleEventAndCheckLicense(PackageEvent $event): void
    {
        $package = $this->resolvePackage($event);

        if ($package === null) {
            if ($event->getIO()->isVerbose()) {
                $event->getIO()->writeError(
                    '<info>The plugin code was invoked by a not handled event. This most likely is an error in the plugin code. Please report at: https://github.com/MetaSyntactical/composer-plugin-license-check/issues</info>'
                );
            }

            return;
        }

        if ($package->getName() === self::PLUGIN_PACKAGE_NAME) {
            // Skip license check. It is assumed that the licence checker itself is
            // added to the dependencies on purpose and therefore the license of the
            // license checker is provided with (MIT) is accepted.
            return;
        }

        $packageLicenses = [];
        if (is_a($package, CompletePackageInterface::class)) {
            $packageLicenses = $package->getLicense();
        }

        $allowedToUse = true;
        if ($this->deniedLicenses) {
            $allowedToUse = !array_intersect($packageLicenses, $this->deniedLicenses);
        }
        if ($allowedToUse && $this->allowedLicenses) {
            $allowedToUse = (bool) array_intersect($packageLicenses, $this->allowedLicenses);
        }

        if ($package->getName() === 'metasyntactical/composer-plugin-license-check') {
            $allowedToUse = true;
        }

        if (!$allowedToUse) {
            if (!array_key_exists($package->getPrettyName(), $this->allowedPackages)) {
                throw new LicenseNotAllowedException(
                    sprintf(
                        'ERROR: Licenses "%s" of package "%s" are not allowed to be used in the project. Installation failed.',
                        implode(', ', $packageLicenses),
                        $package->getPrettyName()
                    )
                );
            }
            $this->io->writeError(
                sprintf(
                    'WARNING: Licenses "%s" of package "%s" are not allowed to be used in the project but the package has been explicitly allowed.',
                    implode(', ', $packageLicenses),
                    $package->getPrettyName()
                )
            );
        }
    }

    private function getConfig(RootPackageInterface $package): ComposerConfig
    {
        $config = $package->getExtra()[self::PLUGIN_PACKAGE_NAME] ?? [];
        assert(is_array($config));
        /** @psalm-var array{allow-list?: list<mixed>, deny-list?: list<mixed>, allowed-packages?: list<mixed>} $config */

        return new ComposerConfig($config);
    }

    private function resolvePackage(PackageEvent $event): ?PackageInterface
    {
        $operation = $event->getOperation();

        if ($operation instanceof InstallOperation) {
            $package = $operation->getPackage();

            if ($package->getName() === self::PLUGIN_PACKAGE_NAME) {
                $this->composer->getEventDispatcher()->addSubscriber($this);
                if ($event->getIO()->isVerbose()) {
                    $event->getIO()->writeError('<info>The Metasyntactical LicenseCheck Plugin has been enabled.</info>');
                }
            }

            return $package;
        }

        if ($operation instanceof UpdateOperation) {
            return $operation->getTargetPackage();
        }

        return null;
    }
}
