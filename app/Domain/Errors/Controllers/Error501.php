<?php

namespace Leantime\Domain\Errors\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller as FrontcontrollerCore;

class Error501 extends Controller
{
    /**
     * @throws \Exception
     *
     * @return void
     */
    public function run(): void
    {
        FrontcontrollerCore::setResponseCode(501);
        $this->tpl->display('errors.error501', layout:'error');
    }
}
