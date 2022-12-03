<?php

namespace leantime\plugins\services {

    use leantime\plugins\repositories;

    class motivationalQuotes
    {
        public function __construct() {
            $this->quotesRepo = new repositories\motivationalQuotes();
        }


        public function getRandomQuote() {

            $availableQuotes = $this->quotesRepo->getAllQuotes();

            $numberOfQuotes = count($availableQuotes);
            $randomNumber = rand(0, $numberOfQuotes);

            return $availableQuotes[$randomNumber];


        }
    }

}
