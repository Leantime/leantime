<?php

namespace Leantime\Core\Exceptions;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Whoops\Handler\PrettyPageHandler;

class WhoopsHandler
{
    /**
     * Create a new Whoops handler for debug mode.
     *
     * @return \Whoops\Handler\PrettyPageHandler
     */
    public function forDebug()
    {
        return tap(new PrettyPageHandler(), function ($handler) {
            $handler->handleUnconditionally(true);

            $this->registerApplicationPaths($handler)
                ->registerBlacklist($handler)
                ->registerEditor($handler);
        });
    }

    /**
     * Register the application paths with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerApplicationPaths($handler)
    {
        $handler->setApplicationPaths(
            array_flip($this->directoriesExceptVendor())
        );

        return $this;
    }

    /**
     * Get the application paths except for the "vendor" directory.
     *
     * @return array
     */
    protected function directoriesExceptVendor()
    {
        return Arr::except(
            array_flip((new Filesystem())->directories(APP_ROOT)),
            [APP_ROOT."/vendor"]
        );
    }

    /**
     * Register the blacklist with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerBlacklist($handler)
    {
        foreach (config('debug_blacklist', config('debug_hide', [])) as $key => $secrets) {
            foreach ($secrets as $secret) {
                $handler->blacklist($key, $secret);
            }
        }

        return $this;
    }

    /**
     * Register the editor with the handler.
     *
     * @param  \Whoops\Handler\PrettyPageHandler $handler
     * @return $this
     */
    protected function registerEditor($handler)
    {

        $editor = config('editor');
        if (config('editor', false)) {
            $handler->setEditor(config('editor'));
        }else{
            $handler->setEditor("phpstorm");
        }

        return $this;
    }
}
