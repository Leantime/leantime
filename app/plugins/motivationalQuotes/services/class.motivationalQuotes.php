<?php

namespace leantime\plugins\services;

use leantime\plugins\repositories;
use leantime\plugins\models;

/**
 * motivational quotes service
 */
class motivationalQuotes
{
    /**
     * constructor
     *
     * @param repositories\motivationalQuotes $quotesRepo
     * @return self
     */
    public function __construct(repositories\motivationalQuotes $quotesRepo)
    {
        $this->quotesRepo = $quotesRepo;
    }

    /**
     * get random quote
     *
     * @return models\motivationalQuotes\quote
     */
    public function getRandomQuote(): models\motivationalQuotes\quote
    {
        $availableQuotes = $this->quotesRepo->getAllQuotes();

        $numberOfQuotes = count($availableQuotes) - 1;
        $randomNumber = rand(0, $numberOfQuotes);

        return $availableQuotes[$randomNumber];
    }
}
