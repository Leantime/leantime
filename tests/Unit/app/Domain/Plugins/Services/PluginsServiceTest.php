<?php

namespace Unit\app\Domain\Plugins\Services;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as HttpResponse;
use Leantime\Domain\Plugins\Models\MarketplacePlugin;
use Leantime\Domain\Plugins\Services\Plugins as PluginService;
use Unit\TestCase;

/**
 * Unit tests for the Plugins service helpers extracted during the
 * thin-controller refactor (buildMarketplacePluginFromRequest, isBundle,
 * parseMarketplaceError, performPluginAction).
 */
class PluginsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_build_marketplace_plugin_from_request_decodes_json_fields(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $plugin = $service->buildMarketplacePluginFromRequest([
            'identifier' => 'acme-plugin',
            'name' => 'Acme Plugin',
            'categories' => json_encode([['slug' => 'reporting', 'name' => 'Reporting']]),
        ]);

        $this->assertInstanceOf(MarketplacePlugin::class, $plugin);
        $this->assertSame('acme-plugin', $plugin->identifier);
        $this->assertSame('Acme Plugin', $plugin->name);
        $this->assertSame([['slug' => 'reporting', 'name' => 'Reporting']], $plugin->categories);
    }

    public function test_build_marketplace_plugin_from_request_keeps_non_json_strings(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $plugin = $service->buildMarketplacePluginFromRequest([
            'name' => 'Just a string',
            'excerpt' => 'Not json',
        ]);

        $this->assertSame('Just a string', $plugin->name);
        $this->assertSame('Not json', $plugin->excerpt);
    }

    public function test_build_marketplace_plugin_from_request_ignores_disallowed_control_fields(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $plugin = $service->buildMarketplacePluginFromRequest([
            'identifier' => 'acme-plugin',
            'type' => 'system',
            'marketplaceUrl' => 'https://attacker.example.com',
        ]);

        // Allowlisted field is set; sensitive control fields are ignored and keep their defaults.
        $this->assertSame('acme-plugin', $plugin->identifier);
        $this->assertSame('marketplace', $plugin->type);
        $this->assertSame('', $plugin->marketplaceUrl);
    }

    public function test_is_bundle_true_when_bundles_category_present(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $plugin = new MarketplacePlugin;
        $plugin->categories = [
            ['slug' => 'reporting'],
            ['slug' => 'bundles'],
        ];

        $this->assertTrue($service->isBundle($plugin));
    }

    public function test_is_bundle_false_when_no_bundles_category(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $plugin = new MarketplacePlugin;
        $plugin->categories = [
            ['slug' => 'reporting'],
        ];

        $this->assertFalse($service->isBundle($plugin));
    }

    public function test_parse_marketplace_error_extracts_clean_message(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $exception = new RequestException(
            new HttpResponse(new PsrResponse(500, [], '{"error":"License invalid"}'))
        );

        $this->assertSame('License invalid', $service->parseMarketplaceError($exception));
    }

    public function test_parse_marketplace_error_falls_back_to_generic_message(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $exception = new RequestException(
            new HttpResponse(new PsrResponse(200, [], 'not-json'))
        );

        $this->assertSame('There was an error installing the plugin', $service->parseMarketplaceError($exception));
    }

    public function test_perform_plugin_action_returns_success_descriptor(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class, [
            'enablePlugin' => fn () => true,
        ]);

        $this->assertSame(
            ['notification.plugin_enable_success', 'success'],
            $service->performPluginAction('enable', 5)
        );
    }

    public function test_perform_plugin_action_returns_error_descriptor_on_failure(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class, [
            'disablePlugin' => fn () => false,
        ]);

        $this->assertSame(
            ['notification.plugin_disable_error', 'error'],
            $service->performPluginAction('disable', 5)
        );
    }

    public function test_perform_plugin_action_rejects_unknown_action(): void
    {
        /** @var PluginService $service */
        $service = $this->make(PluginService::class);

        $this->expectException(\InvalidArgumentException::class);

        $service->performPluginAction('explode', 5);
    }

    /**
     * #3594: compiled Blade views are keyed on the source mtime, which a phar plugin does not
     * change across releases, so an upgraded plugin kept running its old templates. Any plugin
     * change must drop the compiled views (and only the compiled .php files).
     */
    public function test_clear_cache_removes_compiled_views(): void
    {
        $compiledDir = sys_get_temp_dir().'/lt-compiled-views-'.uniqid();
        mkdir($compiledDir);
        file_put_contents($compiledDir.'/abc123.php', '<?php // compiled');
        file_put_contents($compiledDir.'/.gitignore', '*');
        config(['view.compiled' => $compiledDir]);
        $this->app->instance(\Leantime\Core\Auth\Permissions\PermissionService::class, $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, ['flushCache' => null]));

        $this->make(\Leantime\Domain\Plugins\Services\Plugins::class)->clearCache();

        $this->assertFileDoesNotExist($compiledDir.'/abc123.php');
        $this->assertFileExists($compiledDir.'/.gitignore');

        @unlink($compiledDir.'/.gitignore');
        @rmdir($compiledDir);
    }

    /**
     * The role->permission grant map lives on the instance cache store (it comes from the
     * instance's own database), so a plugin change must flush it through the permission engine
     * rather than forgetting keys on the shared installation store.
     */
    public function test_clear_cache_flushes_the_permission_engine_cache(): void
    {
        $flushed = 0;
        $this->app->instance(\Leantime\Core\Auth\Permissions\PermissionService::class, $this->make(\Leantime\Core\Auth\Permissions\PermissionService::class, [
            'flushCache' => function () use (&$flushed): void {
                $flushed++;
            },
        ]));

        $this->make(\Leantime\Domain\Plugins\Services\Plugins::class)->clearCache();

        $this->assertSame(1, $flushed);
    }
}
