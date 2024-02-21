<?php

namespace Vedmant\FeedReader\Tests;

use Orchestra\Testbench\TestCase as TestBenchTestCase;

class TestCase extends TestBenchTestCase
{
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    protected function getPackageProviders($app)
    {
        return [\Vedmant\FeedReader\FeedReaderServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'FeedReader' => \Vedmant\FeedReader\Facades\FeedReader::class,
        ];
    }
}
