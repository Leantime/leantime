<?php

namespace Leantime\Domain\Gamecenter\Controllers;

/**
 *
 */
class Launch extends \Leantime\Core\Controller\Controller
{

    public function init() {

    }

    public function get($params) {

        if(isset($params['game']) && $params['game'] == "snake"){
            return $this->tpl->displayPartial("gamecenter.launchSnake");
        }

    }

}

