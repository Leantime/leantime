<?php

namespace Leantime\Plugins\MotivationalQuotes\Models;

/**
 * quote model
 *
 * @package Leantime\Plugins\MotivationalQuotes\Models
 */
class Quote
{
    /**
     * @var string
     */
    public string $author;

    /**
     * @var string
     */
    public string $quote;

    /**
     * __construct
     *
     * @param string $quote
     * @param string $author
     * @return self
     */
    public function __construct(string $quote = "", string $author = "")
    {
        $this->author = $author;
        $this->quote = $quote;
    }
}
