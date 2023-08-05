<?php

namespace leantime\plugins\repositories;

use leantime\plugins;
use leantime\plugins\models;

/**
 * motivationalQuotes Repository
 */
class motivationalQuotes
{
    /**
     * constructor
     *
     * @return self
     */
    public function __construct()
    {
        //Get DB Instance
        //$this->db = app()->make(core\db::class);
    }

    /**
     * getAllQuotes
     *
     * @return leantime\plugins\models\motivationalQuotes\quote[]
     */
    public function getAllQuotes(): array
    {
        $quotes = [
            "To live is the rarest thing in the world. Most people exist, that is all." => "Oscar Wilde",
            "You cannot find peace by avoiding life" => "Virginia Woolf",
            "The strongest principle of growth lies in the human choice" => "George Eliot",
            "Focus more on your desire than on your doubt, and the dream will take care of itself." => "Mark Twain",
            "We have to continually be jumping off cliffs and developing our wings on the way down." => "Kurt Vonnegut",
            "Don't bend; don't water it down; don't try to make it logical; don't edit your own soul according to the fashion. Rather, follow your most intense obsessions mercilessly." => "Franz Kafka",
            "Keep away from people who try to belittle your ambitions. Small people always do that, but the really great make you feel that you, too, can become great." => "Mark Twain",
            "Trust our heart if the seas catch fire, live by love though the stars walk backwards." => "E. E. Cummings",
            "One day I will find the right words, and they will be simple." => "Jack Kerouac",
            "I can be changed by what happens to me. But I refuse to be reduced by it." => "Maya Angelou",
            "The most common way people give up their power is by thinking they don't have any." => "Alice Walker",
            "I want to taste and glory in each day, and never be afraid to experience pain." => "Sylvia Plath",
            "If I waited for perfection, I would never write a word." => "Margaret Atwood",
            "How wonderful it is that nobody need wait a single moment before starting to improve the world." => "Anne Frank",
            "We are what we repeatedly do. Excellence, then, is not an act, but a habit." => "Aristotle",
        ];

            //Results could be fetched from the db here.
            $quotes = array_map(
                fn ($quote, $author) => app()->make(
                    models\motivationalQuotes\quote::class,
                    ['quote' => $quote, 'author' => $author]
                ),
                array_keys($quotes),
                array_values($quotes)
            );

        return $quotes;
    }
}
