<?php

namespace Tests\Support\Helper;

use Codeception\Module;
use Leantime\Core\Application;
use Illuminate\Support\Facades\Session;

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

    public function setCSRFToken()
    {
        $token = bin2hex(random_bytes(32));
        Session::put('_token', $token);
        Session::save();

        // Set the token in the cookie as well
        $this->getModule('WebDriver')->setCookie('XSRF-TOKEN', $token);
    }
}
