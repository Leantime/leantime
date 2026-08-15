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
            $label = $this->sanitizeLabelKey($params['label']);

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
            $labelKey = $this->sanitizeLabelKey($_GET['label']);
            $sanitizedString = htmlspecialchars(strip_tags($params['newLabel'] ?? ''), ENT_QUOTES, 'UTF-8');

            $this->settingsSvc->saveProjectLabel($module, $labelKey, $sanitizedString, (int) session('currentProject'));

            $this->tpl->setNotification($this->language->__('notifications.label_changed_successfully'), 'success');
        }

        $this->tpl->assign('currentLabel', $sanitizedString);

        return $this->tpl->displayPartial('setting.editBoxDialog');
    }

    /**
     * Normalize a label key without destroying it.
     *
     * Ticket labels are keyed by integers, but idea labels are keyed by the canvasTypes
     * strings ('idea', 'validation', …). The previous
     * `(int) filter_var(..., FILTER_SANITIZE_NUMBER_INT)` turned every idea key into 0,
     * which then got persisted and permanently 500'd the board (#3685). Numeric keys are
     * returned as ints so ticket labels behave exactly as before; anything else is passed
     * through as a trimmed string for the service to look up. An unknown key simply
     * matches nothing, so this cannot be used to write an arbitrary label.
     */
    private function sanitizeLabelKey(mixed $label): int|string
    {
        if (! is_scalar($label)) {
            return '';
        }

        $label = trim((string) $label);

        return is_numeric($label) ? (int) $label : $label;
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
