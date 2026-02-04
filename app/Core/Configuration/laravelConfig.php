<?php

use Laravel\Sanctum\Sanctum;

return [
    'app' => [
        'providers' => [
            /*
             * Application Service Providers...
             */
            \Leantime\Core\Application\AppServiceProvider::class,

            \Leantime\Core\Support\LoadMacrosServiceProvider::class,

            \Leantime\Core\Cache\CacheServiceProvider::class, // \Illuminate\Cache\CacheServiceProvider::class,
            \Leantime\Core\Cache\Redis\RedisServiceProvider::class,
            \SocialiteProviders\Manager\ServiceProvider::class,

            \Leantime\Core\Console\ConsoleSupportProvider::class,
            \Illuminate\Cookie\CookieServiceProvider::class,
            // \Illuminate\Database\DatabaseServiceProvider::class,
            \Leantime\Core\Encryption\EncryptionServiceProvider::class,
            \Leantime\Core\Files\FileSystemServiceProvider::class,

            \Illuminate\Foundation\Providers\FoundationServiceProvider::class,
            \Illuminate\Hashing\HashServiceProvider::class,
            \Laravel\Sanctum\SanctumServiceProvider::class,
            \Leantime\Core\Auth\Tokens\SanctumServiceProvider::class,

            \Illuminate\Notifications\NotificationServiceProvider::class,
            \Illuminate\Pagination\PaginationServiceProvider::class,

            \Illuminate\Pipeline\PipelineServiceProvider::class,
            \Illuminate\Queue\QueueServiceProvider::class,

            \Leantime\Core\Sessions\SessionServiceProvider::class,

            \Illuminate\Validation\ValidationServiceProvider::class,

            \Leantime\Core\Auth\AuthenticationServiceProvider::class,
            \Leantime\Core\Middleware\RateLimiter::class,
            \Leantime\Core\Database\DatabaseServiceProvider::class,
            \Leantime\Core\i18n\LanguageServiceProvider::class,
            // \Leantime\Core\Providers\RouteServiceProvider::class,

            \Leantime\Core\Routing\FrontcontrollerServiceProvider::class,
            \Leantime\Core\UI\ViewsServiceProvider::class,
            \Leantime\Core\UI\TemplateServiceProvider::class,
            \Leantime\Core\UI\ThemeServiceProvider::class,

            // Console support
            \Illuminate\Database\MigrationServiceProvider::class,
            Illuminate\Foundation\Providers\ComposerServiceProvider::class,

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
            'LEAN_OIDC_CLIENT_SECRET',
            'LEAN_S3_SECRET',
            'LEAN_S3_KEY',
            'LEAN_EMAIL_SMTP_USERNAME',
            'LEAN_ACCOUNTS_DB_NAME',
            'LEAN_STRIPE_KEY',
            'LEAN_ACCOUNTS_DB_PASSWORD',
            'LEAN_STRIPE_SECRET',
            'LEAN_MAINKEY',
            'LEAN_BEDROCK_SECRET',
            'LEAN_CRISP_IDENTIFIER',
            'LEAN_CRISP_WEBSITE_ID',
            'LEAN_CRISP_KEY',
            'LEAN_SENTRY_DSN',
            'LEAN_BEDROCK_KEY',
            'LEAN_DB_DATABASE',
            'LEAN_MAINKEY',
            'LEAN_ACCOUNTS_DB_HOST',
            'LEAN_REDIS_HOST',
            'LEAN_DB_USER',
            'LEAN_DB_HOST',
            'LEAN_BEDROCK_AGENT',
            'LEAN_BEDROCK_AGENT_ALIAS',
            'LEAN_ACCOUNTS_DB_USER',
            'username',
            'password',
            'host',
        ],
        '_SERVER' => [
            'LEAN_EMAIL_SMTP_PASSWORD',
            'LEAN_DB_PASSWORD',
            'LEAN_SESSION_PASSWORD',
            'LEAN_OIDC_CLIENT_SECRET',
            'LEAN_S3_SECRET',
            'LEAN_S3_KEY',
            'LEAN_EMAIL_SMTP_USERNAME',
            'LEAN_ACCOUNTS_DB_NAME',
            'LEAN_STRIPE_KEY',
            'LEAN_ACCOUNTS_DB_PASSWORD',
            'LEAN_STRIPE_SECRET',
            'LEAN_MAINKEY',
            'LEAN_BEDROCK_SECRET',
            'LEAN_CRISP_IDENTIFIER',
            'LEAN_CRISP_WEBSITE_ID',
            'LEAN_CRISP_KEY',
            'LEAN_SENTRY_DSN',
            'LEAN_BEDROCK_KEY',
            'LEAN_DB_DATABASE',
            'LEAN_MAINKEY',
            'LEAN_ACCOUNTS_DB_HOST',
            'LEAN_REDIS_HOST',
            'LEAN_DB_USER',
            'LEAN_DB_HOST',
            'LEAN_BEDROCK_AGENT',
            'LEAN_BEDROCK_AGENT_ALIAS',
            'LEAN_ACCOUNTS_DB_USER',
            'username',
            'password',
            'host',
        ],
        '_POST' => [
            'password',
        ],
    ],
    'filesystems' => [

        /*
        |--------------------------------------------------------------------------
        | Default Filesystem Disk
        |--------------------------------------------------------------------------
        |
        | Here you may specify the default filesystem disk that should be used
        | by the framework. The "local" disk, as well as a variety of cloud
        | based disks are available to your application for file storage.
        |
        */

        'default' => env('LEAN_USE_S3', false) ? 's3' : 'local',

        /*
        |--------------------------------------------------------------------------
        | Filesystem Disks
        |--------------------------------------------------------------------------
        |
        | Below you may configure as many filesystem disks as necessary, and you
        | may even configure multiple disks for the same driver. Examples for
        | most supported storage drivers are configured here for reference.
        |
        | Supported drivers: "local", "ftp", "sftp", "s3"
        |
        */
        'disks' => [
            'local' => [
                'driver' => 'local',
                'root' => base_path('userfiles'),
                'serve' => true,
                'throw' => false,
                'report' => false,
                'renameFiles' => env('LEAN_FILESYSTEM_RENAME_FILES', true),
            ],
            'public' => [
                'driver' => 'local',
                'root' => public_path('userfiles'),
                'url' => env('LEAN_APP_URL').'/userfiles',
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
                'renameFiles' => env('LEAN_FILESYSTEM_RENAME_FILES', true),
            ],
            's3' => [
                'driver' => 's3',
                'key' => env('LEAN_S3_KEY'),
                'secret' => env('LEAN_S3_SECRET'),
                'region' => env('LEAN_S3_REGION'),
                'bucket' => env('LEAN_S3_BUCKET'),
                'url' => env('LEAN_S3_URL') ?? env('LEAN_S3_END_POINT'),
                'endpoint' => env('LEAN_S3_END_POINT'),
                'use_path_style_endpoint' => env('LEAN_S3_USE_PATH_STYLE_ENDPOINT', false),
                'throw' => false,
                'report' => false,
                'prefix' => env('LEAN_S3_FOLDER_NAME', ''),
                'renameFiles' => env('LEAN_FILESYSTEM_RENAME_FILES', true),
            ],
            'null' => [
                'driver' => 'null',
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Symbolic Links
        |--------------------------------------------------------------------------
        |
        | Here you may configure the symbolic links that will be created when the
        | `storage:link` Artisan command is executed. The array keys should be
        | the locations of the links and the values should be their targets.
        |
        */

        'links' => [
            public_path('storage') => storage_path('app/public/userfiles'),
        ],

        'url' => [
            'expiration' => 60,
        ],
        'cache' => [
            'enabled' => true,
            'duration' => 60,
        ],

    ],
    'logging' => [
        'channels' => [
            'stack' => [
                'driver' => 'stack',
                'channels' => explode(',', env('LEAN_LOG_CHANNELS', 'single,syslog,sentry')),
                'ignore_exceptions' => false,
            ],
            'single' => [
                'driver' => 'daily',
                'path' => storage_path('logs/leantime.log'),
                'level' => env('LEAN_DEBUG', 0) ? 'debug' : 'error',
                'permission' => 0664,
                'days' => 5,
                'bubble' => true,
            ],
            'syslog' => [
                'driver' => 'syslog',
                'level' => env('LEAN_DEBUG', 0) ? 'debug' : 'error',
                'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
                'replace_placeholders' => true,
            ],
            'deprecations' => [
                'driver' => 'single',
                'path' => storage_path('logs/deprecations.log'),
            ],
            'sentry' => [
                'driver' => 'sentry',
                'level' => env('LEAN_SENTRY_LOG_LEVEL', 'error'),
                'bubble' => true,
            ],
            'stderr' => [
                'driver' => 'monolog',
                'handler' => \Monolog\Handler\StreamHandler::class,
                'with' => [
                    'stream' => 'php://stderr',
                ],
                'level' => env('LEAN_DEBUG', 0) ? 'debug' : 'error',
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
        'prefix' => '',

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

        'lifetime' => env('LEAN_SESSION_EXPIRATION', 480), // 8 hours

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
                    // Connection pooling and management options
                    PDO::ATTR_PERSISTENT => env('LEAN_DB_PERSISTENT_CONNECTIONS', true),
                    PDO::ATTR_TIMEOUT => env('LEAN_DB_CONNECTION_TIMEOUT', 30),
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::MYSQL_ATTR_FOUND_ROWS => true,
                    // Connection limits and timeouts
                    PDO::MYSQL_ATTR_INIT_COMMAND => sprintf(
                        'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, @@session.wait_timeout = %d, @@session.interactive_timeout = %d',
                        env('LEAN_DB_IDLE_TIMEOUT', 3600),
                        env('LEAN_DB_IDLE_TIMEOUT', 3600)
                    ),
                ]) : [],
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'url' => env('LEAN_DB_URL'),
                'host' => env('LEAN_DB_HOST', '127.0.0.1'),
                'port' => env('LEAN_DB_PORT', '5432'),
                'database' => env('LEAN_DB_DATABASE', 'leantime'),
                'username' => env('LEAN_DB_USER', 'postgres'),
                'password' => env('LEAN_DB_PASSWORD', ''),
                'charset' => env('LEAN_DB_CHARSET', 'utf8'),
                'prefix' => '',
                'prefix_indexes' => true,
                'search_path' => env('LEAN_DB_SCHEMA', 'public'),
                'sslmode' => env('LEAN_DB_SSLMODE', 'prefer'),
                'options' => extension_loaded('pdo_pgsql') ? array_filter([
                    PDO::ATTR_PERSISTENT => env('LEAN_DB_PERSISTENT_CONNECTIONS', true),
                    PDO::ATTR_TIMEOUT => env('LEAN_DB_CONNECTION_TIMEOUT', 30),
                ]) : [],
            ],
        ],
        'migrations' => 'migrations',

    ],
    'redis' => [
        /*
       |--------------------------------------------------------------------------
       | Redis Databases
       |--------------------------------------------------------------------------
       |
       | Redis is an open source, fast, and advanced key-value store that also
       | provides a richer body of commands than a typical key-value system
       | such as Memcached. You may define your connection settings here.
       |
        */
        'client' => 'phpredis',
        'options' => [
            'parameters' => ['timeout' => 1.0],
            'cluster' => 'redis',
            'context' => [],
            'compression' => 3, // Redis::COMPRESSION_LZ4
        ],
        'default' => [
            'url' => env('LEAN_REDIS_URL', ''),
            'scheme' => env('LEAN_REDIS_SCHEME', 'tls'),
            'host' => env('LEAN_REDIS_HOST', '127.0.0.1'),
            'username' => env('LEAN_REDIS_USERNAME'),
            'password' => env('LEAN_REDIS_PASSWORD'),
            'port' => env('LEAN_REDIS_PORT', '6379'),
            'database' => env('LEAN_REDIS_DB', '0'),
            'read_timeout' => 1.0,
            'prefix' => 'leantime_cache',
        ],
    ],
    'auth' => [
        'defaults' => [
            'guard' => 'leantime',
            'passwords' => 'users',
        ],
        'guards' => [
            'leantime' => [
                'driver' => 'leantime',
                'provider' => 'leantimeUsers',
            ],
            'sanctum' => [
            ],
            'jsonRpc' => [
                'driver' => 'jsonRpc',
                'provider' => 'leantimeUsers',
            ],
        ],
        'providers' => [
            'leantimeUsers' => [
                'driver' => 'leantimeUsers',
            ],
        ],
    ],
    'sentry' => [
        // @see https://docs.sentry.io/product/sentry-basics/dsn-explainer/
        'dsn' => env('LEAN_SENTRY_LARAVEL_DSN', env('LEAN_SENTRY_DSN')),

        // @see https://spotlightjs.com/
        // 'spotlight' => env('LEAN_SENTRY_SPOTLIGHT', false),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#logger
        // 'logger' => (env('LEAN_DEBUG') === "1" || env('LEAN_DEBUG') === "true") ? Sentry\Logger\DebugFileLogger::class : null, // By default this will log to `storage_path('logs/sentry.log')`

        // The release version of your application
        // Example with dynamic git hash: trim(exec('git --git-dir ' . base_path('.git') . ' log --pretty="%h" -n1 HEAD'))
        'release' => 'leantime-backend@'.get_release_version(),

        // When left empty or `null` the Laravel environment will be used (usually discovered from `APP_ENV` in your `.env`)
        'environment' => env('LEAN_ENV', 'dev'),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#sample-rate
        'sample_rate' => env('LEAN_SENTRY_SAMPLE_RATE') === null ? 1.0 : (float) env('LEAN_SENTRY_SAMPLE_RATE'),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#traces-sample-rate
        'traces_sample_rate' => env('LEAN_SENTRY_TRACES_SAMPLE_RATE') === null ? null : (float) env('LEAN_SENTRY_TRACES_SAMPLE_RATE'),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#profiles-sample-rate
        'profiles_sample_rate' => env('LEAN_SENTRY_PROFILES_SAMPLE_RATE') === null ? null : (float) env('LEAN_SENTRY_PROFILES_SAMPLE_RATE'),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#send-default-pii
        'send_default_pii' => env('LEAN_SENTRY_SEND_DEFAULT_PII', false),

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#ignore-exceptions
        // 'ignore_exceptions' => [],

        // @see: https://docs.sentry.io/platforms/php/guides/laravel/configuration/options/#ignore-transactions
        'ignore_transactions' => [
            // Ignore Laravel's default health URL
            '/up',
            '/cron/run',
            '/notifications/news-badge/get',
            'notifications/news/get',
        ],

        // Breadcrumb specific configuration
        'breadcrumbs' => [
            // Capture Laravel logs as breadcrumbs
            'logs' => env('LEAN_SENTRY_BREADCRUMBS_LOGS_ENABLED', true),

            // Capture Laravel cache events (hits, writes etc.) as breadcrumbs
            'cache' => env('LEAN_SENTRY_BREADCRUMBS_CACHE_ENABLED', false),

            // Capture Livewire components like routes as breadcrumbs
            'livewire' => env('LEAN_SENTRY_BREADCRUMBS_LIVEWIRE_ENABLED', false),

            // Capture SQL queries as breadcrumbs
            'sql_queries' => env('LEAN_SENTRY_BREADCRUMBS_SQL_QUERIES_ENABLED', true),

            // Capture SQL query bindings (parameters) in SQL query breadcrumbs
            'sql_bindings' => env('LEAN_SENTRY_BREADCRUMBS_SQL_BINDINGS_ENABLED', false),

            // Capture queue job information as breadcrumbs
            'queue_info' => env('LEAN_SENTRY_BREADCRUMBS_QUEUE_INFO_ENABLED', true),

            // Capture command information as breadcrumbs
            'command_info' => env('LEAN_SENTRY_BREADCRUMBS_COMMAND_JOBS_ENABLED', true),

            // Capture HTTP client request information as breadcrumbs
            'http_client_requests' => env('LEAN_SENTRY_BREADCRUMBS_HTTP_CLIENT_REQUESTS_ENABLED', true),

            // Capture send notifications as breadcrumbs
            'notifications' => env('LEAN_SENTRY_BREADCRUMBS_NOTIFICATIONS_ENABLED', true),
        ],

        // Performance monitoring specific configuration
        'tracing' => [
            // Trace queue jobs as their own transactions (this enables tracing for queue jobs)
            'queue_job_transactions' => env('LEAN_SENTRY_TRACE_QUEUE_ENABLED', true),

            // Capture queue jobs as spans when executed on the sync driver
            'queue_jobs' => env('LEAN_SENTRY_TRACE_QUEUE_JOBS_ENABLED', true),

            // Capture SQL queries as spans
            'sql_queries' => env('LEAN_SENTRY_TRACE_SQL_QUERIES_ENABLED', true),

            // Capture SQL query bindings (parameters) in SQL query spans
            'sql_bindings' => env('LEAN_SENTRY_TRACE_SQL_BINDINGS_ENABLED', false),

            // Capture where the SQL query originated from on the SQL query spans
            'sql_origin' => env('LEAN_SENTRY_TRACE_SQL_ORIGIN_ENABLED', true),

            // Define a threshold in milliseconds for SQL queries to resolve their origin
            'sql_origin_threshold_ms' => env('LEAN_SENTRY_TRACE_SQL_ORIGIN_THRESHOLD_MS', 100),

            // Capture views rendered as spans
            'views' => env('LEAN_SENTRY_TRACE_VIEWS_ENABLED', true),

            // Capture Livewire components as spans
            'livewire' => env('LEAN_SENTRY_TRACE_LIVEWIRE_ENABLED', false),

            // Capture HTTP client requests as spans
            'http_client_requests' => env('LEAN_SENTRY_TRACE_HTTP_CLIENT_REQUESTS_ENABLED', true),

            // Capture Laravel cache events (hits, writes etc.) as spans
            'cache' => env('LEAN_SENTRY_TRACE_CACHE_ENABLED', true),

            // Capture Redis operations as spans (this enables Redis events in Laravel)
            'redis_commands' => env('LEAN_USE_REDIS', true),

            // Capture where the Redis command originated from on the Redis command spans
            'redis_origin' => env('LEAN_USE_REDIS', true),

            // Capture send notifications as spans
            'notifications' => env('LEAN_SENTRY_TRACE_NOTIFICATIONS_ENABLED', true),

            // Enable tracing for requests without a matching route (404's)
            'missing_routes' => env('LEAN_SENTRY_TRACE_MISSING_ROUTES_ENABLED', false),

            // Configures if the performance trace should continue after the response has been sent to the user until the application terminates
            // This is required to capture any spans that are created after the response has been sent like queue jobs dispatched using `dispatch(...)->afterResponse()` for example
            'continue_after_response' => env('LEAN_SENTRY_TRACE_CONTINUE_AFTER_RESPONSE', true),

            // Enable the tracing integrations supplied by Sentry (recommended)
            'default_integrations' => env('LEAN_SENTRY_TRACE_DEFAULT_INTEGRATIONS_ENABLED', true),
        ],
    ],
    'sanctum' => [

        /*
        |--------------------------------------------------------------------------
        | Stateful Domains
        |--------------------------------------------------------------------------
        |
        | Requests from the following domains / hosts will receive stateful API
        | authentication cookies. Typically, these should include your local
        | and production domains which access your API via a frontend SPA.
        |
        */

        'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
            '%s%s',
            'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,127.0.0.1:8080,::1',
            Sanctum::currentApplicationUrlWithPort()
        ))),

        /*
        |--------------------------------------------------------------------------
        | Sanctum Guards
        |--------------------------------------------------------------------------
        |
        | This array contains the authentication guards that will be checked when
        | Sanctum is trying to authenticate a request. If none of these guards
        | are able to authenticate the request, Sanctum will use the bearer
        | token that's present on an incoming request for authentication.
        |
        */

        'guard' => [],

        /*
        |--------------------------------------------------------------------------
        | Expiration Minutes
        |--------------------------------------------------------------------------
        |
        | This value controls the number of minutes until an issued token will be
        | considered expired. This will override any values set in the token's
        | "expires_at" attribute, but first-party sessions are not affected.
        |
        */

        'expiration' => null,

        /*
        |--------------------------------------------------------------------------
        | Token Prefix
        |--------------------------------------------------------------------------
        |
        | Sanctum can prefix new tokens in order to take advantage of numerous
        | security scanning initiatives maintained by open source platforms
        | that notify developers if they commit tokens into repositories.
        |
        | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
        |
        */

        'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

        /*
        |--------------------------------------------------------------------------
        | Sanctum Middleware
        |--------------------------------------------------------------------------
        |
        | When authenticating your first-party SPA with Sanctum you may need to
        | customize some of the middleware Sanctum uses while processing the
        | request. You may change the middleware listed below as required.
        |
        */

        'middleware' => [
            // 'authenticate_session' => \Leantime\Core\Middleware\AuthenticateSession::class,
            // 'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
            // 'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        ],

    ],
    'queue' => [

        /*
        |--------------------------------------------------------------------------
        | Default Queue Connection Name
        |--------------------------------------------------------------------------
        |
        | Laravel's queue supports a variety of backends via a single, unified
        | API, giving you convenient access to each backend using identical
        | syntax for each. The default queue connection is defined below.
        |
        */

        'default' => env('QUEUE_CONNECTION', 'database'),

        /*
        |--------------------------------------------------------------------------
        | Queue Connections
        |--------------------------------------------------------------------------
        |
        | Here you may configure the connection options for every queue backend
        | used by your application. An example configuration is provided for
        | each backend supported by Laravel. You're also free to add more.
        |
        | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
        |
        */

        'connections' => [

            'sync' => [
                'driver' => 'sync',
            ],

            'database' => [
                'driver' => 'database',
                'connection' => env('LEAN_DB_DEFAULT_CONNECTION', 'mysql'),
                'table' => 'zp_jobs',
                'queue' => 'default',
                'retry_after' => 90,
                'after_commit' => false,
            ],

            'beanstalkd' => [
                'driver' => 'beanstalkd',
                'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
                'queue' => 'default',
                'retry_after' => 90,
                'block_for' => 0,
                'after_commit' => false,
            ],

            'sqs' => [
                'driver' => 'sqs',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
                'queue' => 'default',
                'suffix' => env('SQS_SUFFIX'),
                'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
                'after_commit' => false,
            ],

            'redis' => [
                'driver' => 'redis',
                'connection' => 'default',
                'queue' => 'default',
                'retry_after' => 90,
                'block_for' => null,
                'after_commit' => false,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Job Batching
        |--------------------------------------------------------------------------
        |
        | The following options configure the database and table that store job
        | batching information. These options can be updated to any database
        | connection and table which has been defined by your application.
        |
        */

        'batching' => [
            'database' => env('LEAN_DB_DEFAULT_CONNECTION', 'mysql'),
            'table' => 'job_batches',
        ],

        /*
        |--------------------------------------------------------------------------
        | Failed Queue Jobs
        |--------------------------------------------------------------------------
        |
        | These options configure the behavior of failed queue job logging so you
        | can control how and where failed jobs are stored. Laravel ships with
        | support for storing failed jobs in a simple file or in a database.
        |
        | Supported drivers: "database-uuids", "dynamodb", "file", "null"
        |
        */

        'failed' => [
            'driver' => 'database-uuids',
            'database' => env('LEAN_DB_DEFAULT_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ],

    ],
    'hashing' => [
        'rehash_on_login' => false,
    ],

];
