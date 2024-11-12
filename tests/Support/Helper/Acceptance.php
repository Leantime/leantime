<?php

namespace Tests\Support\Helper;

use Codeception\Module;
use Leantime\Core\Application;

class Acceptance extends Module
{
    protected Application $app;

    public function _initialize()
    {
        $this->app = require dirname(__DIR__, 2) . '/bootstrap.php';
    }

    public function getApplication(): Application
    {
        return $this->app;
    }
}
