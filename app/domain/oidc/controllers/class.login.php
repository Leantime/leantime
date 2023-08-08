<?php

namespace leantime\domain\controllers;

use leantime\core\controller;
use leantime\domain\services;
use leantime\core\frontcontroller;

class login extends controller
{
    private services\oidc $oidc;

    public function init(services\oidc $oidc, frontcontroller $frontcontroller)
    {
        $this->oidc = $oidc;
        $frontcontroller::redirect($this->oidc->buildLoginUrl(), 302);
    }
}
