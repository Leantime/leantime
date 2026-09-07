<?php

namespace Unit\app\Core\Controller;

use Leantime\Core\Auth\Permissions\PermissionEnforcer;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Unit\TestCase;

/**
 * Regression coverage for plugin controller resolution when the plugin's folder
 * name contains an INTERNAL capital.
 *
 * getValidControllerCall() runs the URL's module segment through Str::studly(),
 * which flattens inner capitals: "pgmpro" becomes "Pgmpro", never "PgmPro".
 * Composer's PSR-4 prefix map is case-sensitive, so
 * Leantime\Plugins\Pgmpro\Hxcontrollers\X never resolved and every controller in
 * such a plugin 404'd with "Can't find a valid controller" — the whole of
 * PgmPro's and StrategyPro's modal surface (add dependency, add project, add
 * person, link user).
 *
 * The enabled-plugin record already carries the true folder name, so resolution
 * matches case-insensitively and then uses THAT spelling for the class lookup.
 *
 * Fixture classes are declared at runtime rather than pointing at a real plugin:
 * app/Plugins is a private submodule that CI does not check out, so a test
 * asserting against PgmPro would pass locally and be meaningless on CI.
 *
 * One nuance: because the fixtures are declared up front, class_exists() finds
 * them under EITHER casing (PHP class names are case-insensitive once declared).
 * So these tests assert on the casing of the resolved class path, whereas in
 * production the failure is harder — the class was never declared, the
 * case-sensitive PSR-4 autoloader could not load it, and the request 404'd. Same
 * root cause, same fix; this guards the resolution logic without needing the
 * private submodule on disk.
 */
class FrontcontrollerPluginCasingTest extends TestCase
{
    /** Folder name with an inner capital — the shape that used to fail. */
    private const CASED_FOLDER = 'FixtureCasedPlugin';

    /** What Str::studly() turns the lowercase URL segment into. */
    private const STUDLIED_SEGMENT = 'Fixturecasedplugin';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        // Declare the fixture controllers inside the namespace the resolver builds,
        // so class_exists() behaves exactly as it does for a real plugin.
        if (! class_exists('Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Controllers\\FixtureAction', false)) {
            eval('namespace Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Controllers; class FixtureAction {}');
        }

        if (! class_exists('Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Hxcontrollers\\FixtureModal', false)) {
            eval('namespace Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Hxcontrollers; class FixtureModal {}');
        }
    }

    private function frontcontroller(array $enabledFolderNames): Frontcontroller
    {
        // getClassPath() resolves the plugin service out of the container; stub it
        // so the test needs no database (unit tests have no DB connection).
        $pluginService = $this->createMock(PluginService::class);
        $pluginService->method('getEnabledPlugins')->willReturn(
            array_map(static function (string $folder) {
                $plugin = new \stdClass;
                $plugin->foldername = $folder;

                return $plugin;
            }, $enabledFolderNames)
        );

        app()->instance(PluginService::class, $pluginService);

        // Built by hand: container resolution would pull in the real
        // PermissionEnforcer, which needs a database connection.
        return new Frontcontroller(
            IncomingRequest::create('/', 'GET'),
            $this->createMock(PermissionEnforcer::class),
        );
    }

    /**
     * The bug. Before the fix this returned false, because the resolver looked for
     * Leantime\Plugins\Fixturecasedplugin\Controllers\FixtureAction.
     */
    public function test_plugin_controller_resolves_despite_the_studlied_segment_losing_an_inner_capital(): void
    {
        $fc = $this->frontcontroller([self::CASED_FOLDER]);

        $this->assertSame(
            'Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Controllers\\FixtureAction',
            $fc->getClassPath('Controllers', self::STUDLIED_SEGMENT, 'FixtureAction')
        );
    }

    /**
     * The surface that actually 404'd. getControllerType() returns 'Hxcontrollers'
     * for a real HTMX request, which makes the FIRST plugin lookup the
     * Hxcontrollers one rather than the fallback — so this has to be asserted with
     * that controllerType, not with 'Controllers'.
     */
    public function test_plugin_hxcontroller_resolves_on_the_htmx_controller_type(): void
    {
        $fc = $this->frontcontroller([self::CASED_FOLDER]);

        $this->assertSame(
            'Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Hxcontrollers\\FixtureModal',
            $fc->getClassPath('Hxcontrollers', self::STUDLIED_SEGMENT, 'FixtureModal')
        );
    }

    /** And via the fallback, for a non-HTMX request that names an Hx controller. */
    public function test_plugin_hxcontroller_resolves_through_the_fallback(): void
    {
        $fc = $this->frontcontroller([self::CASED_FOLDER]);

        $this->assertSame(
            'Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Hxcontrollers\\FixtureModal',
            $fc->getClassPath('Controllers', self::STUDLIED_SEGMENT, 'FixtureModal')
        );
    }

    /**
     * getEnabledPlugins() is typed `mixed` and its payload passes through a
     * plugin-modifiable filter; cached entries can also unserialize to
     * __PHP_Incomplete_Class. Malformed entries must be skipped, not fataled on —
     * routing resolution runs for every request.
     */
    public function test_malformed_enabled_plugin_entries_are_skipped_not_fataled(): void
    {
        $incomplete = unserialize('O:22:"SomeClassThatIsNotHere":0:{}');

        $pluginService = $this->createMock(PluginService::class);
        $pluginService->method('getEnabledPlugins')->willReturn([
            $incomplete,                                  // __PHP_Incomplete_Class
            ['foldername' => 'ArrayShapedPlugin'],        // array shape
            'a-bare-string',                              // neither
            (object) ['name' => 'no-foldername-key'],     // object missing the key
            (object) ['foldername' => self::CASED_FOLDER],
        ]);
        app()->instance(PluginService::class, $pluginService);

        $fc = new Frontcontroller(
            IncomingRequest::create('/', 'GET'),
            $this->createMock(PermissionEnforcer::class),
        );

        // Reaches the well-formed entry at the end without fataling on the others.
        $this->assertSame(
            'Leantime\\Plugins\\'.self::CASED_FOLDER.'\\Hxcontrollers\\FixtureModal',
            $fc->getClassPath('Hxcontrollers', self::STUDLIED_SEGMENT, 'FixtureModal')
        );
    }

    /**
     * The case-insensitive match must not become case-blind about WHICH plugin:
     * a module that is not in the enabled list still resolves to false.
     */
    public function test_a_plugin_that_is_not_enabled_still_does_not_resolve(): void
    {
        $fc = $this->frontcontroller([self::CASED_FOLDER]);

        $this->assertFalse($fc->getClassPath('Controllers', 'SomeOtherPlugin', 'FixtureAction'));
    }

    /**
     * A known action in an enabled plugin that genuinely has no such class must
     * still fail, rather than the looser matching inventing a hit.
     */
    public function test_unknown_action_in_an_enabled_plugin_does_not_resolve(): void
    {
        $fc = $this->frontcontroller([self::CASED_FOLDER]);

        $this->assertFalse($fc->getClassPath('Controllers', self::STUDLIED_SEGMENT, 'NoSuchAction'));
    }

    /**
     * Core domain controllers resolve before the plugin branch is reached and must
     * be unaffected.
     */
    public function test_domain_controllers_resolve_ahead_of_the_plugin_branch(): void
    {
        $fc = $this->frontcontroller([]);

        $this->assertSame(
            \Leantime\Domain\Calendar\Controllers\ShowMyCalendar::class,
            $fc->getClassPath('Controllers', 'Calendar', 'ShowMyCalendar')
        );
    }
}
