<?php

namespace leantime\plugins\repositories {

    use leantime\plugins;
    use leantime\plugins\models;

    class motivationalQuotes
    {
        public function __construct() {
            //Get DB Instance
            //$this->db = core\db::getInstance();
        }


        public function getAllQuotes() {

            //Results could be fetched from the db here.
            $quotes = array(
                new models\motivationalQuotes\quote("To live is the rarest thing in the world. Most people exist, that is all.", "Oscar Wilde"),
                new models\motivationalQuotes\quote("You cannot find peace by avoiding life", "Virginia Woolf"),
                new models\motivationalQuotes\quote("The strongest principle of growth lies in the human choice", "George Eliot"),
                new models\motivationalQuotes\quote("Focus more on your desire than on your doubt, and the dream will take care of itself.", "Mark Twain"),
                new models\motivationalQuotes\quote("We have to continually be jumping off cliffs and developing our wings on the way down.", "Kurt Vonnegut"),
                new models\motivationalQuotes\quote("Don't bend; don't water it down; don't try to make it logical; don't edit your own soul according to the fashion. Rather, follow your most intense obsessions mercilessly.", "Franz Kafka"),
                new models\motivationalQuotes\quote("Keep away from people who try to belittle your ambitions. Small people always do that, but the really great make you feel that you, too, can become great.", "Mark Twain"),
                new models\motivationalQuotes\quote("Trust our heart if the seas catch fire, live by love though the stars walk backwards.", "E. E. Cummings"),
                new models\motivationalQuotes\quote("One day I will find the right words, and they will be simple.", "Jack Kerouac"),
                new models\motivationalQuotes\quote("I can be changed by what happens to me. But I refuse to be reduced by it.", "Maya Angelou"),
                new models\motivationalQuotes\quote("The most common way people give up their power is by thinking they don't have any.", "Alice Walker"),
                new models\motivationalQuotes\quote("I want to taste and glory in each day, and never be afraid to experience pain.", "Sylvia Plath"),
                new models\motivationalQuotes\quote("If I waited for perfection, I would never write a word.", "Margaret Atwood"),
                new models\motivationalQuotes\quote("How wonderful it is that nobody need wait a single moment before starting to improve the world.", "Anne Frank"),
                new models\motivationalQuotes\quote("We are what we repeatedly do. Excellence, then, is not an act, but a habit.", "Aristotle")
            );

            return $quotes;

        }

    }

}
