<?php

namespace Leantime\Plugins\MotivationalQuotes\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Plugins\MotivationalQuotes\Repositories\MotivationalQuotes as MotivationalQuotesRepository;
use Leantime\Plugins\MotivationalQuotes\Models\Quote as QuoteModel;

/**
 * motivational quotes service
 */
class MotivationalQuotes
{
    private MotivationalQuotesRepository $quotesRepo;

    /**
     * constructor
     *
     * @param MotivationalQuotesRepository $quotesRepo
     * @return void
     */
    public function __construct(MotivationalQuotesRepository $quotesRepo)
    {
        $this->quotesRepo = $quotesRepo;
    }

    /**
     * get random quote
     *
     * @return QuoteModel
     * @throws BindingResolutionException
     */
    public function getRandomQuote(): QuoteModel
    {
        $availableQuotes = $this->quotesRepo->getAllQuotes();

        $numberOfQuotes = count($availableQuotes) - 1;
        $randomNumber = rand(0, $numberOfQuotes);

        return $availableQuotes[$randomNumber];
    }
}
