<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Connector\Permissions\ConnectorPermissions;

class Providers extends Controller
{
    /**
     * get - handle get requests
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function get($params)
    {
        return $this->tpl->displayPartial('connectors.providers');
    }

    /**
     * post - handle post requests
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function post($params)
    {
        return $this->tpl->displayPartial('connectors.providers');
    }
}
