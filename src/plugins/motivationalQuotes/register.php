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
        /**
         * Events must have a handler.
         *
         * Please see the executeHandlers method in
         * src/core/class.events.php to see all the different
         * ways this can be formatted.
         */
        public function handle($payload)
        {
            var_dump('exit'); exit;

            // code here
            $motivationalQuotesSvc = new \leantime\plugins\services\motivationalQuotes();
            $randomQuote = $motivationalQuotesSvc->getRandomQuote();
            echo"<br />";

            echo "<h3 style='font-style: italic;'><i class='fa-solid fa-quote-left'></i> ".$randomQuote->quote."</h3>";
            echo "<small>- ".$randomQuote->author."</small>";
        }
    }

    \leantime\core\events::add_event_listener("core.template.dispatchTplEvent.afterWelcomeMessage", new motivationalQuotes());

}


