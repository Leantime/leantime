<?php

namespace Leantime\Domain\Modulemanager\Controllers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Modules\ModuleRegistry;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Symfony\Component\HttpFoundation\Response;

class Notavailable extends Controller
{
    private ModuleRegistry $moduleRegistry;

    private PermissionService $permissionService;

    /**
     * Initializes dependencies.
     */
    public function init(ModuleRegistry $moduleRegistry, PermissionService $permissionService): void
    {
        $this->moduleRegistry = $moduleRegistry;
        $this->permissionService = $permissionService;
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
            // Link to the Modules tab only for callers who can open project settings.
            $this->tpl->assign('projectSettingsUrl', $this->permissionService->currentUserCan(ProjectsPermissions::EDIT)
                ? BASE_URL.'/projects/showProject/'.(int) session('currentProject').'#modules'
                : null);

            return $this->tpl->display('modulemanager.notavailable', responseCode: 404);
        }

        $redirect = BASE_URL.'errors/error404';
        $redirect = DispatchesEvents::dispatch_filter('notAvailableRedirect', $redirect, $params);

        return Frontcontroller::redirect($redirect);
    }
}
