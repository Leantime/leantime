<?php

namespace Leantime\Domain\Modulemanager\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\Response;

class Notavailable extends Controller
{
    private ModuleRegistry $moduleRegistry;

    /**
     * Initializes dependencies.
     */
    public function init(ModuleRegistry $moduleRegistry): void
    {
        $this->moduleRegistry = $moduleRegistry;
    }

    /**
     * Explains that a module is switched off in the current project (?module=wiki), or
     * redirects to the error page or a filtered alternative (plugins use this for upgrade
     * messages).
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $module = isset($params['module']) ? $this->moduleRegistry->get((string) $params['module']) : null;

        if ($module !== null) {
            $this->tpl->assign('moduleTitle', __($module->title));
            $this->tpl->assign('projectSettingsUrl', Auth::userIsAtLeast(Roles::$manager)
                ? BASE_URL.'/projects/showProject/'.(int) session('currentProject').'#modules'
                : null);

            return $this->tpl->display('modulemanager.notavailable', responseCode: 404);
        }

        $redirect = BASE_URL.'errors/error404';
        $redirect = DispatchesEvents::dispatch_filter('notAvailableRedirect', $redirect, $params);

        return Frontcontroller::redirect($redirect);
    }
}
