<?php

use Leantime\Core\Providers\Cache;
use Leantime\Core\Providers\Redis;
use Leantime\Core\Providers\Session;

return [
    'app' => [
        'providers' => [
            /*
             * Application Service Providers...
             */
            \Leantime\Core\Providers\AppServiceProvider::class,


            \Leantime\Core\Providers\Cache::class, //\Illuminate\Cache\CacheServiceProvider::class,
            \Leantime\Core\Providers\Redis::class,

            \Leantime\Core\Providers\ConsoleSupport::class,
            \Illuminate\Cookie\CookieServiceProvider::class,
            //\Illuminate\Database\DatabaseServiceProvider::class,
            \Leantime\Core\Providers\EncryptionServiceProvider::class,
            \Leantime\Core\Providers\FileSystemServiceProvider::class,

            \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            //\Illuminate\Mail\MailServiceProvider::class,
            \Illuminate\Notifications\NotificationServiceProvider::class,
            \Illuminate\Pagination\PaginationServiceProvider::class,
            //\Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
            \Illuminate\Pipeline\PipelineServiceProvider::class,
            //\Illuminate\Queue\QueueServiceProvider::class,

            //\Illuminate\Redis\RedisServiceProvider::class,

            //\Illuminate\Session\SessionServiceProvider::class,
            \Leantime\Core\Providers\Session::class,

            //\Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
            //\Illuminate\View\ViewServiceProvider::class,

            \Leantime\Core\Providers\Auth::class,
            \Leantime\Core\Providers\RateLimiter::class,
            \Leantime\Core\Providers\Db::class,
            \Leantime\Core\Providers\Language::class,
            //\Leantime\Core\Providers\RouteServiceProvider::class,

            \Leantime\Core\Providers\Frontcontroller::class,
            \Leantime\Core\Providers\Views::class,
            \Leantime\Core\Providers\TemplateServiceProvider::class,
        ],
        'name' => env('LEAN_SITENAME', 'Leantime'),
        'locale' => env('LEAN_LANGUAGE', 'en-US'),
        'url' => env('LEAN_APP_URL', ''),
        'timezone' => env('LEAN_DEFAULT_TIMEZONE', 'America/Los_Angeles'),
        'env' => env('LEAN_ENV', ''),
        'debug' => env('LEAN_DEBUG', 0),
        'key' => env('LEAN_SESSION_PASSWORD', '123'),

    ],
    'debug_blacklist' => [
        '_ENV' => [
            'LEAN_EMAIL_SMTP_PASSWORD',
            'LEAN_DB_PASSWORD',
            'LEAN_SESSION_PASSWORD',
            'LEAN_OIDC_CLIEND_SECRET',
            'LEAN_S3_SECRET',
        ],

        '_SERVER' => [
            'LEAN_EMAIL_SMTP_PASSWORD',
            'LEAN_DB_PASSWORD',
            'LEAN_SESSION_PASSWORD',
            'LEAN_OIDC_CLIEND_SECRET',
            'LEAN_S3_SECRET',
            'LEAN_S3_KEY',
            'LEAN_EMAIL_SMTP_USERNAME',
            'ACCOUNTS_DB_NAME',
            'STRIPE_KEY',
            'ACCOUNTS_DB_PASSWORD',
            'STRIPE_SECRET',
            'MAINKEY',
        ],
        '_POST' => [
            'password',
        ],
    ],
    'logging' => [
        'deprecations' => [
            'channel' => 'deprecations',
            'trace' => false,
        ],
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                // Add the Sentry log channel to the stack
                'channels' => ['single', 'sentry'],
            ],
            'single' => [
                'driver' => 'daily',
                'path' => storage_path('logs/leantime.log'),
                'permission' => 0664,
                'days' => 5,
            ],
            'deprecations' => [
                'driver' => 'single',
                'path' => storage_path('logs/deprecations.log'),
            ],
            'slack' => [
                'driver' => 'slack',
                'url' => '',
                'username' => 'Laravel Log',
                'emoji' => ':boom:',
                'level' => 'critical',
                'replace_placeholders' => true,
            ],
            'sentry' => [
                'driver' => 'sentry',
                'level' => 'error',
                'bubble' => true,
            ],
        ],
        'default' => 'stack',
    ],
    'cache' => [

        /*
        |--------------------------------------------------------------------------
        | Default Cache Store
        |--------------------------------------------------------------------------
        |
        | This option controls the default cache store that will be used by the
        | framework. This connection is utilized if another isn't explicitly
        | specified when running a cache operation inside the application.
        |
        */

        'default' => 'installation',

        /*
        |--------------------------------------------------------------------------
        | Cache Stores
        |--------------------------------------------------------------------------
        |
        | Here you may define all of the cache "stores" for your application as
        | well as their drivers. You may even define multiple stores for the
        | same cache driver to group types of items stored in your caches.
        |
        | Supported drivers: "array", "database", "file", "memcached",
        |                    "redis", "dynamodb", "octane", "null"
        |
        */

        'stores' => [

            'installation' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/installation/data'),
            ],

            'sessions' => [
                'driver' => 'file',
                'path' => storage_path('framework/sessions'),
            ],

            'array' => [
                'driver' => 'array',
                'serialize' => false,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Key Prefix
        |--------------------------------------------------------------------------
        |
        | When utilizing the APC, database, memcached, Redis, and DynamoDB cache
        | stores, there might be other applications using the same cache. For
        | that reason, you may prefix every cache key to avoid collisions.
        |
        */
        'prefix' => 'leantime_cache_',

    ],
    'session' => [

        /*
        |--------------------------------------------------------------------------
        | Default Session Driver
        |--------------------------------------------------------------------------
        |
        | This option controls the default session "driver" that will be used on
        | requests. By default, we will use the lightweight native driver but
        | you may specify any of the other wonderful drivers provided here.
        |
        | Supported: "file", "cookie", "database", "apc",
        |            "memcached", "redis", "dynamodb", "array"
        |
        */

        'driver' => 'file',

        /*
        |--------------------------------------------------------------------------
        | Session Lifetime
        |--------------------------------------------------------------------------
        |
        | Here you may specify the number of minutes that you wish the session
        | to be allowed to remain idle before it expires. If you want them
        | to immediately expire on the browser closing, set that option.
        |
        */

        'lifetime' => 480, //8 hours

        'expire_on_close' => false,

        /*
        |--------------------------------------------------------------------------
        | Session Encryption
        |--------------------------------------------------------------------------
        |
        | This option allows you to easily specify that all of your session data
        | should be encrypted before it is stored. All encryption will be run
        | automatically by Laravel and you can use the Session like normal.
        |
        */

        'encrypt' => false,

        /*
        |--------------------------------------------------------------------------
        | Session File Location
        |--------------------------------------------------------------------------
        |
        | When using the native session driver, we need a location where session
        | files may be stored. A default has been set for you but a different
        | location may be specified. This is only needed for file sessions.
        |
        */

        'files' => storage_path('framework/sessions'),

        /*
        |--------------------------------------------------------------------------
        | Session Database Table
        |--------------------------------------------------------------------------
        |
        | When using the "database" session driver, you may specify the table we
        | should use to manage the sessions. Of course, a sensible default is
        | provided for you; however, you are free to change this as needed.
        |
        */

        'table' => 'sessions',

        /*
        |--------------------------------------------------------------------------
        | Session Cache Store
        |--------------------------------------------------------------------------
        |
        | While using one of the framework's cache driven session backends you may
        | list a cache store that should be used for these sessions. This value
        | must match with one of the application's configured cache "stores".
        |
        | Affects: "apc", "dynamodb", "memcached", "redis"
        |
        */

        'store' => 'sessions',

        /*
        |--------------------------------------------------------------------------
        | Session Sweeping Lottery
        |--------------------------------------------------------------------------
        |
        | Some session drivers must manually sweep their storage location to get
        | rid of old sessions from storage. Here are the chances that it will
        | happen on a given request. By default, the odds are 2 out of 100.
        |
        */

        'lottery' => [2, 1000],

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Name
        |--------------------------------------------------------------------------
        |
        | Here you may change the name of the cookie used to identify a session
        | instance by ID. The name specified here will get used every time a
        | new session cookie is created by the framework for every driver.
        |
        */

        'cookie' => 'leantime_session',

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Path
        |--------------------------------------------------------------------------
        |
        | The session cookie path determines the path for which the cookie will
        | be regarded as available. Typically, this will be the root path of
        | your application but you are free to change this when necessary.
        |
        */

        'path' => '/',

        /*
        |--------------------------------------------------------------------------
        | Session Cookie Domain
        |--------------------------------------------------------------------------
        |
        | Here you may change the domain of the cookie used to identify a session
        | in your application. This will determine which domains the cookie is
        | available to in your application. A sensible default has been set.
        |
        */

        'domain' => '',

        /*
        |--------------------------------------------------------------------------
        | HTTPS Only Cookies
        |--------------------------------------------------------------------------
        |
        | By setting this option to true, session cookies will only be sent back
        | to the server if the browser has a HTTPS connection. This will keep
        | the cookie from being sent to you when it can't be done securely.
        |
        */

        'secure' => env('LEAN_SESSION_SECURE', false),

        /*
        |--------------------------------------------------------------------------
        | HTTP Access Only
        |--------------------------------------------------------------------------
        |
        | Setting this value to true will prevent JavaScript from accessing the
        | value of the cookie and the cookie will only be accessible through
        | the HTTP protocol. You are free to modify this option if needed.
        |
        */

        'http_only' => true,

        /*
        |--------------------------------------------------------------------------
        | Same-Site Cookies
        |--------------------------------------------------------------------------
        |
        | This option determines how your cookies behave when cross-site requests
        | take place, and can be used to mitigate CSRF attacks. By default, we
        | will set this value to "lax" since this is a secure default value.
        |
        | Supported: "lax", "strict", "none", null
        |
        */

        'same_site' => 'lax',

        /*
        |--------------------------------------------------------------------------
        | Partitioned Cookies
        |--------------------------------------------------------------------------
        |
        | Setting this value to true will tie the cookie to the top-level site for
        | a cross-site context. Partitioned cookies are accepted by the browser
        | when flagged "secure" and the Same-Site attribute is set to "none".
        |
        */

        'partitioned' => false,
    ],
    'view' => [

        'cache' => true,

        'compiled_extension' => 'php',

        /*
        |--------------------------------------------------------------------------
        | Compiled View Path
        |--------------------------------------------------------------------------
        |
        | This option determines where all the compiled Blade templates will be
        | stored for your application. Typically, this is within the storage
        | directory. However, as usual, you are free to change this value.
        |
        */

        'compiled' => realpath(storage_path('framework/views')),

    ],
    'redis' => [
        'client' => 'phpredis',
        'options' => [
            'cluster' => 'redis',
            'context' => [],
            'compression' => 3, // Redis::COMPRESSION_LZ4
        ],
        'default' => [
            'url' => env('LEAN_REDIS_URL', ''),
            'scheme' => env('LEAN_REDIS_SCHEME', 'tls'),
            'host' => env('LEAN_REDIS_HOST', '127.0.0.1'),
            'password' => env('LEAN_REDIS_PASSWORD', null),
            'port' => env('LEAN_REDIS_PORT', '127.0.0.1'),
            'database' => '0',
            'prefix' => 'leantime_cache',
        ],
    ],
    'database' => [
        'default' => env('LEAN_DB_DEFAULT_CONNECTION', 'mysql'),
        /*
        |--------------------------------------------------------------------------
        | Database Connections
        |--------------------------------------------------------------------------
        |
        | Below are all of the database connections defined for your application.
        | An example configuration is provided for each database system which
        | is supported by Laravel. You're free to add / remove connections.
        |
        */
        'connections' => [
            'sqlite' => [
                'driver' => 'sqlite',
                'url' => env('LEAN_DB_URL'),
                'database' => database_path('database.sqlite'),
                'prefix' => '',
                'foreign_key_constraints' => env('LEAN_DB_FOREIGN_KEYS', true),
                'busy_timeout' => null,
                'journal_mode' => null,
                'synchronous' => null,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'url' => env('LEAN_DB_URL'),
                'host' => env('LEAN_DB_HOST', '127.0.0.1'),
                'port' => env('LEAN_DB_PORT', '3306'),
                'database' => env('LEAN_DB_DATABASE', 'laravel'),
                'username' => env('LEAN_DB_USER', 'root'),
                'password' => env('LEAN_DB_PASSWORD', ''),
                'unix_socket' => env('LEAN_DB_SOCKET', ''),
                'charset' => env('LEAN_DB_CHARSET', 'utf8mb4'),
                'collation' => env('LEAN_DB_COLLATION', 'utf8mb4_unicode_ci'),
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => false,
                'engine' => 'InnoDB',
                'sslmode' => env('LEAN_DB_SSLMODE', ''),
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => env('LEAN_DB_MYSQL_ATTR_SSL_VERIFY_SERVER', false),
                    PDO::MYSQL_ATTR_SSL_KEY => env('LEAN_DB_MYSQL_ATTR_SSL_KEY'),
                    PDO::MYSQL_ATTR_SSL_CERT => env('LEAN_DB_MYSQL_ATTR_SSL_CERT'),
                    PDO::MYSQL_ATTR_SSL_CA => env('LEAN_DB_MYSQL_ATTR_SSL_CA'),
                    PDO::ATTR_EMULATE_PREPARES => true,
                ]) : [],
            ],
        ],
    ],
];
