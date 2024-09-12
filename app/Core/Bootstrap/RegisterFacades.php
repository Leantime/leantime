<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Facade;

class RegisterFacades
{
    /**
     * Bootstrap the given application.
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        Facade::clearResolvedInstances();

        Facade::setFacadeApplication($app);

        $aliasArr = $app->make('config')->get('app.aliases', []);
        $packageManifestArr = $app->make(\Illuminate\Foundation\PackageManifest::class)->aliases();

        AliasLoader::getInstance($aliasArr)->register();
    }
}
