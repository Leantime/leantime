<?php

namespace leantime\domain\controllers;

/**
 * menu Class displays the menu
 *
 */

use leantime\core;

class showMenu
{

    /**
     * run - display template and edit data
     *
     * @access public
     */
    public function run()
    {

        $tpl = new core\template();

        $tpl->displayPartial('general.showMenu');

    }

}

