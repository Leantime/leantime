<?php

namespace Leantime\Core\Console;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Providers\ComposerServiceProvider;
use Illuminate\Foundation\Providers\ConsoleSupportServiceProvider;
use Leantime\Core\Console\CliServiceProvider;

class ConsoleSupportProvider extends ConsoleSupportServiceProvider implements DeferrableProvider
{
    /**
     * The provider class names.
     *
     * @var string[]
     */
    protected $providers = [
        CliServiceProvider::class,
        ComposerServiceProvider::class,
    ];
}
