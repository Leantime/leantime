<?php

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */

//Create function for the event


function showQuote($payload)
{
    // code here
    $motivationalQuotesSvc = new \leantime\plugins\services\motivationalQuotes();
    $randomQuote = $motivationalQuotesSvc->getRandomQuote();
    echo"<div class='motivationalQuote' style='margin-bottom:20px;'><br />";
    echo "<p>Quote of the day:</p>";
    echo "<p style='font-style: italic; font-weight:normal;'><i class='fa-solid fa-quote-left'></i> " . $randomQuote->quote . "</p>";
    echo "<small>- " . $randomQuote->author . "</small></div>";
}

//Register event listener
\leantime\core\events::add_event_listener("core.template.tpl.dashboard.home.afterWelcomeMessage", 'showQuote', 5);

