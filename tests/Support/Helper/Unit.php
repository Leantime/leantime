<?php

namespace Tests\Support\Helper;

use Codeception\Module;
use Leantime\Infrastructure\Application\Application;

class Unit extends Module
{
    /**
     * @var Application
     */
    protected $app;

    public function _initialize()
    {
        $this->app = bootstrap_minimal_app();
    }

    public function getApplication()
    {
        return $this->app;
    }

    public function haveInDatabase($table, array $data)
    {
        return $this->getModule('Db')->haveInDatabase($table, $data);
    }

    public function seeInDatabase($table, array $criteria)
    {
        return $this->getModule('Db')->seeInDatabase($table, $criteria);
    }

    public function dontSeeInDatabase($table, array $criteria)
    {
        return $this->getModule('Db')->dontSeeInDatabase($table, $criteria);
    }
}
