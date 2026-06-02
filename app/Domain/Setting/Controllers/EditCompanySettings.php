<?php

namespace Leantime\Domain\Setting\Controllers;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Setting\Permissions\SettingPermissions;
use Leantime\Domain\Setting\Services\Setting as SettingService;

class EditCompanySettings extends Controller
{
    private ApiService $APIService;

    private SettingService $settingsSvc;

    private Theme $theme;

    /**
     * init - initialize private variables
     */
    public function init(
        ApiService $APIService,
        SettingService $settingsSvc,
        Theme $theme,
    ): void {
        $this->APIService = $APIService;
        $this->settingsSvc = $settingsSvc;
        $this->theme = $theme;
    }

    /**
     * get - handle get requests
     */
    #[RequiresPermission(SettingPermissions::COMPANY_VIEW, global: true)]
    public function get($params)
    {
        if (isset($_GET['resetLogo'])) {
            $this->settingsSvc->resetLogo();

            return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings#look');
        }

        $companySettingsView = $this->settingsSvc->getCompanySettings($this->theme->getLogoUrl());

        $apiKeys = $this->APIService->getAPIKeys();

        $this->tpl->assign('apiKeys', $apiKeys);
        $this->tpl->assign('languageList', $this->language->getLanguageList());
        $this->tpl->assign('companySettings', $companySettingsView['companySettings']);
        $this->tpl->assign('notificationCategories', Notification::NOTIFICATION_CATEGORIES);
        $this->tpl->assign('defaultNotificationTypes', $companySettingsView['defaultNotificationTypes']);
        $this->tpl->assign('defaultRelevance', $companySettingsView['defaultRelevance']);
        $this->tpl->assign('relevanceLevels', [
            Notification::RELEVANCE_ALL => 'label.notifications_all_activity',
            Notification::RELEVANCE_MY_WORK => 'label.notifications_my_work',
        ]);

        return $this->tpl->display('setting.editCompanySettings');
    }

    /**
     * post - handle post requests
     */
    #[RequiresPermission(SettingPermissions::COMPANY_EDIT, global: true)]
    public function post($params)
    {
        // The telemetry opt-out path keys off the raw POST flag; mirror it into params.
        $params['telemetryActive'] = isset($_POST['telemetryActive']);

        $saved = $this->settingsSvc->saveCompanySettings($params);

        if ($saved) {
            $this->tpl->setNotification($this->language->__('notifications.company_settings_edited_successfully'), 'success');
        }

        return Frontcontroller::redirect(BASE_URL.'/setting/editCompanySettings');
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
