<?php

use Leantime\Core\Events\EventDispatcher;

EventDispatcher::add_filter_listener("leantime.domain.auth.template.userInvite.welcomeText", function($content, $params){
    $language = app()->make(\Leantime\Core\Language::class);
    return $language->__("text.welcome_to_leantime_content");
});

EventDispatcher::add_filter_listener("leantime.domain.auth.template.userInvite2.welcomeText", function($content, $params){
    $language = app()->make(\Leantime\Core\Language::class);
    return $language->__("text.welcome_to_leantime_content");
});

EventDispatcher::add_filter_listener("leantime.domain.auth.template.userInvite3.welcomeText", function($content, $params){
    $language = app()->make(\Leantime\Core\Language::class);
    return $language->__("text.welcome_to_leantime_content");
});

EventDispatcher::add_filter_listener("leantime.domain.auth.template.userInvite4.welcomeText", function($content, $params){
    $language = app()->make(\Leantime\Core\Language::class);
    return $language->__("text.welcome_to_leantime_content");
});

EventDispatcher::add_filter_listener("leantime.domain.auth.template.userInvite5.welcomeText", function($content, $params){
    $language = app()->make(\Leantime\Core\Language::class);
    return $language->__("text.welcome_to_leantime_content");
});

EventDispatcher::add_filter_listener("leantime.domain.auth.*.belowWelcomeText", function($content, $params){

    $quotes = [];
    $quotes[] = "\"It's the first project management app I've used for more than a week, and it makes sense too.\"<br /><br />- Interior Designer";
    $quotes[] = "\"For me, Leantime is very cool, because it is lean. Not 3 million options to think about. The more you put in, the more it could be overloaded.\"<br /><br />- Open Source User";
    $quotes[] = "\"We are a small digital marketing agency and have been using Leantime for a couple of months after switching from ClickUp. Getting great feedback from our clients.\"<br /><br />- CEO";

    $random = rand(0,2);

    return '
            <div class="socialProofContent">
                <i>'.$quotes[$random].'</i>
            </div>
    ';
});


