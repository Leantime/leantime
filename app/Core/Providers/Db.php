<?php

namespace Leantime\Core\Providers;

use Illuminate\Support\ServiceProvider;

class Db extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(\Leantime\Core\Db\Db::class, \Leantime\Core\Db\Db::class);
    }


}
