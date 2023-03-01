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
        try {
        $this->oidc->callback($code);
        } catch(Exception $ex) {
            echo '<pre>';
            echo $ex->getMessage();
            echo $ex->getTraceAsString();
        }
    }

}