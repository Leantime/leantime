<?php

namespace Leantime\Domain\Plugins\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

class Marketplace extends Controller
{
    public function get(): Response
    {

        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $this->tpl->assign('plugins', []);

        return $this->tpl->display('plugins.marketplace');
    }
}
