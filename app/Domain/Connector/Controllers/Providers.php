<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Http\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;

class Providers extends Controller
{
    /**
     * constructor - initialize private variables
     */
    public function init()
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
    }

    /**
     * get - handle get requests
     */
    public function get($params)
    {
        return $this->tpl->displayPartial('connectors.providers');
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {
        return $this->tpl->displayPartial('connectors.providers');
    }
}
