<?php

use Illuminate\Support\Str;

\Leantime\Core\Events::add_filter_listener("leantime.core.template.tpl.dashboard.home.settingsLink", function () {
    return [
        "module" => "widgets",
        "action" => "widgetManager",
        "url" => Str::finish(CURRENT_URL, '/') . "#/widgets/widgetManager",
        "label" => "links.dashboard_settings",
    ];
});
