<?php

/**
 * MotivationalQuotes
 *
 * Register Events here
 */

\leantime\core\events::add_event_listener(
    //Register event listener
    "core.template.tpl.dashboard.home.afterWelcomeMessage",
    //Create function for the event
    function ($payload) {
        // code here
        $motivationalQuotesSvc = app()->make(\leantime\plugins\services\motivationalQuotes::class);
        $randomQuote = $motivationalQuotesSvc->getRandomQuote();
        echo"<div class='motivationalQuote' style='margin-bottom:20px;'><br />";
        echo "<p>Quote of the day:</p>";
        echo "<p style='font-style: italic; font-weight:normal;'><i class='fa-solid fa-quote-left'></i> " . $randomQuote->quote . "</p>";
        echo "<small>- " . $randomQuote->author . "</small></div>";
    },
    5
);
