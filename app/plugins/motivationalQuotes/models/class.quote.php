<?php

namespace leantime\plugins\models\motivationalQuotes {

    class quote
    {
        public $author;
        public $quote;

        public function __construct($quote = "", $author = "")
        {
            $this->author = $author;
            $this->quote = $quote;
        }
    }
}
