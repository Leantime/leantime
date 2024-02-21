<?php

namespace Vedmant\FeedReader\Facades;

use Illuminate\Support\Facades\Facade;

class FeedReader extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Vedmant\FeedReader\FeedReader::class;
    }
}
