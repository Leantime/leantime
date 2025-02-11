<?php

namespace Leantime\Core\Providers;

use Illuminate\Database\DatabaseServiceProvider;

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

        $this->app->bind(\Leantime\Core\Db\Db::class, function () {
            return new \Leantime\Core\Db\Db(app());
        });
    }
}
