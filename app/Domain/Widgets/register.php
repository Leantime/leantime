<?php

use Leantime\Core\Events\EventDispatcher;

EventDispatcher::addFilterListener('leantime.domain.menu.composers.menu.with.settingsLink', function ($settingsLink, $params) {

    if (isset($params['type']) && $params['type'] == 'personal') {
        return [
            'module' => 'widgets',
            'action' => 'widgetManager',
            'url' => CURRENT_URL.'#/widgets/widgetManager',
            'label' => 'links.dashboard_settings',
        ];
    }

    return $settingsLink;
});
