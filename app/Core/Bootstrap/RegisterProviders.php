<?php

namespace Leantime\Core\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Leantime\Core\Plugins;
use Leantime\Core\Providers\Auth;
use Leantime\Core\Providers\Cache;
use Leantime\Core\Providers\Db;
use Leantime\Core\Providers\EncryptionServiceProvider;
use Leantime\Core\Providers\FileSystemServiceProvider;
use Leantime\Core\Providers\Frontcontroller;
use Leantime\Core\Providers\Language;
use Leantime\Core\Providers\RateLimiter;
use Leantime\Core\Providers\Redis;
use Leantime\Core\Providers\RouteServiceProvider;
use Leantime\Core\Providers\Session;
use Leantime\Core\Providers\TemplateServiceProvider;
use Leantime\Core\Providers\Views;

class RegisterProviders extends \Illuminate\Foundation\Bootstrap\RegisterProviders
{
    /**
     * The service providers that should be merged before registration.
     *
     * @var array
     */
    protected static $merge = [];

    /**
     * The path to the bootstrap provider configuration file.
     *
     * @var string|null
     */
    protected static $bootstrapProviderPath;



    protected static $laravelProviders = [
        \Illuminate\Auth\AuthServiceProvider::class,
        \Illuminate\Broadcasting\BroadcastServiceProvider::class,
        \Illuminate\Bus\BusServiceProvider::class,
        \Illuminate\Cache\CacheServiceProvider::class,
        \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        \Illuminate\Database\DatabaseServiceProvider::class,
        \Illuminate\Encryption\EncryptionServiceProvider::class,
        \Illuminate\Filesystem\FilesystemServiceProvider::class,
        \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        \Illuminate\Hashing\HashServiceProvider::class,
        \Illuminate\Mail\MailServiceProvider::class,
        \Illuminate\Notifications\NotificationServiceProvider::class,
        \Illuminate\Pagination\PaginationServiceProvider::class,
        \Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        \Illuminate\Pipeline\PipelineServiceProvider::class,
        \Illuminate\Queue\QueueServiceProvider::class,
        \Illuminate\Redis\RedisServiceProvider::class,
        \Illuminate\Session\SessionServiceProvider::class,
        \Illuminate\Translation\TranslationServiceProvider::class,
        \Illuminate\Validation\ValidationServiceProvider::class,
        \Illuminate\View\ViewServiceProvider::class
    ];
    protected static $defaultLeantimeProviders = [
        //\Illuminate\Broadcasting\BroadcastServiceProvider::class,
        //\Illuminate\Bus\BusServiceProvider::class,

        Cache::class,
        //\Illuminate\Cache\CacheServiceProvider::class,
        \Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        \Illuminate\Cookie\CookieServiceProvider::class,
        //\Illuminate\Database\DatabaseServiceProvider::class,
        EncryptionServiceProvider::class,
        FileSystemServiceProvider::class,

        \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        \Illuminate\Hashing\HashServiceProvider::class,
        //\Illuminate\Mail\MailServiceProvider::class,
        \Illuminate\Notifications\NotificationServiceProvider::class,
        \Illuminate\Pagination\PaginationServiceProvider::class,
        //\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        \Illuminate\Pipeline\PipelineServiceProvider::class,
        //\Illuminate\Queue\QueueServiceProvider::class,

        Redis::class,
        Session::class,

        //\Illuminate\Redis\RedisServiceProvider::class,
        //\Illuminate\Session\SessionServiceProvider::class,
        //\Illuminate\Translation\TranslationServiceProvider::class,
        \Illuminate\Validation\ValidationServiceProvider::class,
        //\Illuminate\View\ViewServiceProvider::class,

        Auth::class,
        RateLimiter::class,
        Db::class,
        Language::class,
        RouteServiceProvider::class,

        //Frontcontroller::class,
        Views::class,
        TemplateServiceProvider::class,

    ];

    public function bootstrap(Application $app)
    {
        foreach(self::$defaultLeantimeProviders as $provider){
            $app->register($provider);
        }

        $app->registerConfiguredProviders();
    }
}
