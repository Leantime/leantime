<?php

namespace leantime\domain\controllers;

use Exception;
use leantime\core\controller;
use leantime\domain\services;

class callback extends controller {

    private services\oidc $oidc;

    public function init()
    {
        $this->oidc = services\oidc::getInstance();
    }

    public function get($params)
    {
        $code = $_GET['code'];
        $state = $_GET['state'];
        try {
            $this->oidc->callback($code, $state);
        } catch(Exception $ex) {
            echo '<pre>';
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
        }
    }

}