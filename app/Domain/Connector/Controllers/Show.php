<?php

namespace Leantime\Domain\Connector\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Connector\Permissions\ConnectorPermissions;
use Leantime\Domain\Connector\Services;

class Show extends Controller
{
    private Services\Providers $providerService;

    /**
     * constructor - initialize private variables
     */
    public function init(Services\Providers $projectService)
    {
        $this->providerService = $projectService;
    }

    /**
     * get - handle get requests
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function get($params)
    {
        $providers = $this->providerService->getProviders();

        $this->tpl->assign('providers', $providers);

        return $this->tpl->display('connector.show');
    }

    /**
     * post - handle post requests
     */
    #[RequiresPermission(ConnectorPermissions::MANAGE, global: true)]
    public function post($params)
    {
        // Redirect.
    }
}
