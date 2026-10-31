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

    public function test_a_broken_release_is_capped_at_the_last_leantime_version_it_runs_on(): void
    {
        $compatibility = $this->compatibility('3.13.3');
        $customFields151 = ['name' => 'leantime/customFields', 'version' => '1.5.1', 'extra' => ['leantime' => ['requires' => '>=3.13 <4.0']]];

        $range = $compatibility->rangeFor($customFields151);

        $this->assertSame('>=3.13 <4.0 <=3.13.2', $range);
        $this->assertFalse($compatibility->isCompatible($range));
        $this->assertTrue($compatibility->isCompatible($range, '3.13.2'));
    }

    public function test_the_fixed_release_and_other_plugins_keep_their_declared_range(): void
    {
        $compatibility = $this->compatibility('3.13.3');

        $this->assertSame('>=3.13 <4.0', $compatibility->rangeFor(['name' => 'leantime/customFields', 'version' => 'v1.6.0', 'extra' => ['leantime' => ['requires' => '>=3.13 <4.0']]]));
        $this->assertSame('>=3.13 <4.0', $compatibility->rangeFor(['name' => 'leantime/notes', 'version' => '1.5.1', 'extra' => ['leantime' => ['requires' => '>=3.13 <4.0']]]));
        // No version to compare: nothing to cap.
        $this->assertSame(PluginCompatibility::UNDECLARED_RANGE, $compatibility->rangeFor(['name' => 'leantime/customfields']));
    }

    public function test_the_cap_applies_to_every_alternative_of_a_range(): void
    {
        $range = $this->compatibility()->rangeFor(['name' => 'leantime/customfields', 'version' => '1.5.0', 'extra' => ['leantime' => ['requires' => '^3.12 || ^4.0']]]);

        $this->assertSame('^3.12 <=3.13.2 || ^4.0 <=3.13.2', $range);
        $this->assertFalse($this->compatibility()->isCompatible($range, '4.1.0'));
        $this->assertTrue($this->compatibility()->isCompatible($range, '3.13.0'));
    }

    public function test_an_unreadable_range_is_incompatible(): void
    {
        $this->assertFalse($this->compatibility()->isCompatible('not a version range'));
    }
}
