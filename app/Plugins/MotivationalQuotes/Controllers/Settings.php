<?php

namespace Leantime\Plugins\MotivationalQuotes\Controllers;

use Leantime\Core\Controller;

/**
 * Settings Controller for Motivational Quotes Plugin
 *
 * @package    leantime
 * @subpackage plugins
 */
class Settings extends Controller
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
