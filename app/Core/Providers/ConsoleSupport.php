<?php

namespace Leantime\Core\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\AggregateServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Illuminate\Foundation\Providers\ArtisanServiceProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;

class ConsoleSupport extends ConsoleSupportServiceProvider implements DeferrableProvider
{
    /**
     * The provider class names.
     *
     * @var string[]
     */
    protected $providers = [
        CliServices::class,
        ComposerServiceProvider::class,
    ];
}
