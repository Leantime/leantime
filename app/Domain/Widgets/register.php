<?php

use Leantime\Core\Events\EventDispatcher;

EventDispatcher::addFilterListener('leantime.core.template.tpl.dashboard.home.settingsLink', function ($settingsLink) {

    return [
        'module' => 'widgets',
        'action' => 'widgetManager',
        'url' => CURRENT_URL.'#/widgets/widgetManager',
        'label' => 'links.dashboard_settings',
    ];
});
