<?php

/**
 * MotivationalQuotes
 *
 * Register Events here
 *
 */
namespace leantime\domain\events {

    class motivationalQuotes
    {
        //Events must have handle method
        public function handle($payload)
        {
            // code here
            $motivationalQuotesSvc = new \leantime\plugins\services\motivationalQuotes();
            $randomQuote = $motivationalQuotesSvc->getRandomQuote();
            echo"<br />";

            echo "<h3 style='font-style: italic;'><i class='fa-solid fa-quote-left'></i> ".$randomQuote->quote."</h3>";
            echo "<small>- ".$randomQuote->author."</small>";
        }
    }

    \leantime\core\events::add_event_listener("core.template.dispatch_event.afterWelcomeMessage", new motivationalQuotes());

}


