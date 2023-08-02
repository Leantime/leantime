<?php

namespace leantime\plugins\controllers;

use leantime\core;
use leantime\core\controller;

/**
 * Settings Controller for Motivational Quotes Plugin
 *
 * @package    leantime
 * @subpackage plugins
 */
class settings extends controller
{
    /**
     * init
     *
     * @return void
     */
    public function init(): void
    {
    }

    /**
     * get
     *
     * @return void
     */
    public function get(): void
    {
        $this->tpl->display("motivationalQuotes.settings");
    }

    /**
     * post
     *
     * @param array $params
     * @return void
     */
    public function post(array $params): void
    {
    }
}
