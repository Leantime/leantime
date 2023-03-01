<?php

namespace leantime\domain\controllers;

/**
 * menu Class displays the menu
 *
 */

use leantime\core;
use leantime\core\controller;

class showMenu extends controller
{
    /**
     * run - display template and edit data
     *
     * @access public
     */
    public function run()
    {

        $this->tpl->displayPartial('menu.showMenu');
    }
}
