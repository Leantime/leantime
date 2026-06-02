<?php

namespace Leantime\Domain\Setting\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Leantime\Domain\Setting\Services\Setting as SettingService;

class EditBoxLabel extends Controller
{
    private SettingService $settingsSvc;

    /**
     * init - initialize private variables
     */
    public function init(SettingService $settingsSvc): void
    {
        $this->settingsSvc = $settingsSvc;
    }

    /**
     * get - handle get requests
     */
    #[RequiresPermission(SettingPermissions::PROJECT_LABELS)]
    public function get($params)
    {
        $currentLabel = '';

        if (isset($params['module']) && isset($params['label'])) {
            $module = htmlspecialchars($params['module'], ENT_QUOTES, 'UTF-8');
            $label = (int) filter_var($params['label'], FILTER_SANITIZE_NUMBER_INT);

            $currentLabel = $this->settingsSvc->getProjectLabel($module, $label, (int) session('currentProject'));
        }

        $this->tpl->assign('currentLabel', $currentLabel);

        return $this->tpl->displayPartial('setting.editBoxDialog');
    }

    /**
     * post - handle post requests
     */
    #[RequiresPermission(SettingPermissions::PROJECT_LABELS)]
    public function post($params)
    {
        // If module and label are set its an update
        $sanitizedString = '';
        if (isset($_GET['module']) && isset($_GET['label'])) {
            $module = htmlspecialchars($_GET['module'], ENT_QUOTES, 'UTF-8');
            $labelKey = (int) filter_var($_GET['label'], FILTER_SANITIZE_NUMBER_INT);
            $sanitizedString = htmlspecialchars(strip_tags($params['newLabel'] ?? ''), ENT_QUOTES, 'UTF-8');

            $this->settingsSvc->saveProjectLabel($module, $labelKey, $sanitizedString, (int) session('currentProject'));

            $this->tpl->setNotification($this->language->__('notifications.label_changed_successfully'), 'success');
        }

        $this->tpl->assign('currentLabel', $sanitizedString);

        return $this->tpl->displayPartial('setting.editBoxDialog');
    }

    /**
     * put - handle put requests
     */
    public function put($params) {}

    /**
     * delete - handle delete requests
     */
    public function delete($params) {}
}
