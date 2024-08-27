<?php

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Core\Events\EventDispatcher;

EventDispatcher::add_filter_listener("leantime.core.template.*.welcomeText", function ($welcomeText) {

    $language = app()->make(Leantime\Core\Language::class);

    if (Frontcontroller::getCurrentRoute() == "install") {
        $welcomeText = '<h1 class="mainWelcome">'.$language->__('headlines.welcome').'</h1>';
        $subText = "";
        $welcomeText = $welcomeText . $subText;
    }

    if (Frontcontroller::getCurrentRoute() == "install.update") {
        $welcomeText = '<h1 class="mainWelcome">'.$language->__('headlines.welcome').'</h1>';
        $subText = "";
        $welcomeText = $welcomeText . $subText;
    }

    return $welcomeText;
});
