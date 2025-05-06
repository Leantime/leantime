<?php

namespace Tests\Support\Helper;

use Codeception\Module;
use Illuminate\Support\Facades\Session;
use Leantime\Core\Application\Application;

class Acceptance extends Module
{
    protected Application $app;

    public function _initialize()
    {
        $this->app = require dirname(__DIR__, 2).'/bootstrap.php';

        if (! defined('BASE_URL')) {
            define('BASE_URL', 'https://leantime-dev');
        }
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
