<?php

/**
 * MotivationalQuotes
 *
 * Register Events here
 */

\Leantime\Core\Events::add_event_listener(
    //Register event listener
    "leantime.core.template.tpl.dashboard.home.afterWelcomeMessage",

    //Create function for the event
    function ($payload) {
        // code here
        $motivationalQuotesSvc = app()->make(\Leantime\Plugins\MotivationalQuotes\Services\MotivationalQuotes::class);
        $randomQuote = $motivationalQuotesSvc->getRandomQuote();
        echo"<div class='motivationalQuote' style='margin-bottom:20px;'><br />";
        echo "<p>Quote of the day:</p>";
        echo "<p style='font-style: italic; font-weight:normal;'><i class='fa-solid fa-quote-left'></i> " . $randomQuote->quote . "</p>";
        echo "<small>- " . $randomQuote->author . "</small></div>";
    },
    5
);
