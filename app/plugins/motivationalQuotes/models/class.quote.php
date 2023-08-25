<?php

namespace leantime\plugins\models\motivationalQuotes;

/**
 * quote model
 *
 * @package leantime\plugins\models\motivationalQuotes
 */
class quote
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
