<?php

namespace Unit;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Leantime\Core\Application;

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
}
