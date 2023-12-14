<?php

use Leantime\Core\Eventhelpers;

\Leantime\Core\Events::add_filter_listener("leantime.core.template.tpl.dashboard.home.settingsLink", function () {

    return [
        "module" => "widgets",
        "action" => "widgetManager",
        "url" => CURRENT_URL . "#/widgets/widgetManager",
        "label" => "links.dashboard_settings",
    ];
});
