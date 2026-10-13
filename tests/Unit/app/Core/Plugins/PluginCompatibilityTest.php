<?php

namespace Unit\app\Core\Plugins;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Plugins\PluginCompatibility;
use Unit\TestCase;

/**
 * Which Leantime versions a plugin runs on: its composer.json `extra.leantime.requires` range,
 * or 3.x for plugins that declare none (everything released before 3.12).
 */
class PluginCompatibilityTest extends TestCase
{
    private function compatibility(string $leantimeVersion = '3.12.0'): PluginCompatibility
    {
        $appSettings = new AppSettings;
        $appSettings->appVersion = $leantimeVersion;

        return new PluginCompatibility($appSettings);
    }

    public function test_declared_range_is_read_from_composer_extra(): void
    {
        $composer = ['extra' => ['leantime' => ['requires' => ' >=3.12 <4.0 ']]];

        $this->assertSame('>=3.12 <4.0', $this->compatibility()->declaredRange($composer));
        $this->assertSame('>=3.12 <4.0', $this->compatibility()->rangeFor($composer));
    }

    public function test_plugins_without_a_range_are_treated_as_3x(): void
    {
        $compatibility = $this->compatibility();

        $this->assertNull($compatibility->declaredRange(['name' => 'leantime/notes']));
        $this->assertSame(PluginCompatibility::UNDECLARED_RANGE, $compatibility->rangeFor([]));
        $this->assertTrue($compatibility->isCompatible(PluginCompatibility::UNDECLARED_RANGE, '3.12.0'));
        $this->assertTrue($compatibility->isCompatible(PluginCompatibility::UNDECLARED_RANGE, '3.11.0'));
        $this->assertFalse($compatibility->isCompatible(PluginCompatibility::UNDECLARED_RANGE, '4.0.0'));
    }

    public function test_version_is_checked_against_the_range(): void
    {
        $compatibility = $this->compatibility('3.12.0');

        $this->assertTrue($compatibility->isCompatible('>=3.12 <4.0'));
        $this->assertTrue($compatibility->isCompatible('^3.12'));
        $this->assertFalse($compatibility->isCompatible('>=3.13'));
        $this->assertFalse($compatibility->isCompatible('^3.12', '3.11.0'));
        $this->assertTrue($compatibility->isCompatible('^3.12', 'v3.12.4'));
    }

    public function test_an_unreadable_range_is_incompatible(): void
    {
        $this->assertFalse($this->compatibility()->isCompatible('not a version range'));
    }

    public function test_system_plugin_folders_are_checked_by_path(): void
    {
        $base = sys_get_temp_dir().'/lt-system-plugins-'.uniqid();
        foreach (['Supported' => '>=3.0', 'Future' => '>=99.0'] as $folder => $range) {
            mkdir($base.'/'.$folder, 0777, true);
            file_put_contents($base.'/'.$folder.'/composer.json', json_encode(['extra' => ['leantime' => ['requires' => $range]]]));
        }
        mkdir($base.'/NoComposer');

        $compatibility = $this->compatibility('3.12.0');

        $this->assertTrue($compatibility->pluginAtPathIsCompatible($base.'/Supported'));
        $this->assertFalse($compatibility->pluginAtPathIsCompatible($base.'/Future/'));
        // No composer.json = no declared range = the 3.x default: fine on 3.12, not on 4.0.
        $this->assertTrue($compatibility->pluginAtPathIsCompatible($base.'/NoComposer'));
        $this->assertFalse($this->compatibility('4.0.0')->pluginAtPathIsCompatible($base.'/NoComposer'));

        foreach (['Supported', 'Future'] as $folder) {
            unlink($base.'/'.$folder.'/composer.json');
            rmdir($base.'/'.$folder);
        }
        rmdir($base.'/NoComposer');
        rmdir($base);
    }
}
