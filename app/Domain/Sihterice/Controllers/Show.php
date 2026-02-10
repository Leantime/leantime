<?php

namespace Leantime\Domain\Sihterice\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;

class Show extends Controller
{
    /* Display the Sihterice application frontend. */
    public function run()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin], true);

        $this->tpl->assign('sihtericeToken', env('SIHTERICE_TOKEN', ''));
        $this->tpl->assign('sihtericeAddress', env('SIHTERICE_ADDRESS', 'http://localhost:3300'));

        return $this->tpl->display('sihterice.show');
    }
}
