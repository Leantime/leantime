<?php

namespace Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Leantime\Core\Application;
use Leantime\Core\Modules\Contracts\StoresProjectModules;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Core\Modules\ProjectModules;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function setUp(): void
    {

        parent::setUp();

        // Set up default configurations for testing
        config([
            'app.env' => 'testing',
            'cache.default' => 'array',
            'session.driver' => 'array',
            'database.default' => [],
        ]);

    }

    /**
     * Module switches with nothing registered and nothing stored, so every module is on. For
     * services that require ProjectModules in tests that aren't about module switches.
     */
    protected function allModulesOn(): ProjectModules
    {
        $store = $this->getMockBuilder(StoresProjectModules::class)->getMock();

        return new ProjectModules(new ModuleRegistry, $store);
    }
}
