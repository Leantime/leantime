<?php

namespace Leantime\Core\Console;

use Illuminate\Console\Events\ArtisanStarting;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ProcessUtils;

class Application extends \Illuminate\Console\Application {

    public function __construct(Container $laravel, Dispatcher $events, $version)
    {

        $parent = get_parent_class(\Illuminate\Console\Application::class);
        $parent::__construct('Leantime CLI (extends Laravel)', $version);

        $this->laravel = $laravel;
        $this->events = $events;
        $this->setAutoExit(false);
        $this->setCatchExceptions(false);

        $this->events->dispatch(new ArtisanStarting($this));

        $this->bootstrap();
    }

    protected function bootstrap()
    {


        foreach (static::$bootstrappers as $bootstrapper) {
            $bootstrapper($this);
        }
    }

    public static function artisanBinary()
    {
        return ProcessUtils::escapeArgument('bin/leantime');
    }


}
