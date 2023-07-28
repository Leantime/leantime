<?php

namespace leantime\plugins\services {

    use leantime\plugins\repositories;

    class motivationalQuotes
    {
        public function __construct(repositories\motivationalQuotes $quotesRepo)
        {
            $this->quotesRepo = $quotesRepo;
        }

        public function getRandomQuote()
        {
            $availableQuotes = $this->quotesRepo->getAllQuotes();

            $numberOfQuotes = count($availableQuotes) - 1;
            $randomNumber = rand(0, $numberOfQuotes);

            return $availableQuotes[$randomNumber];
        }
    }
}
