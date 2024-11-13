<?php

namespace Leantime\Core\Providers;

use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Support\ServiceProvider;

class Db extends DatabaseServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // Register Laravel's database service first
        parent::register();

        $this->app->singleton(\Leantime\Core\Db\Db::class);
    }
}
