<?php

use \Illuminate\Support;
use Leantime\Core\Providers\Auth;
use Leantime\Core\Providers\Cache;
use Leantime\Core\Providers\ConsoleSupport;
use Leantime\Core\Providers\Db;
use Leantime\Core\Providers\EncryptionServiceProvider;
use Leantime\Core\Providers\FileSystemServiceProvider;
use Leantime\Core\Providers\Frontcontroller;
use Leantime\Core\Providers\Language;
use Leantime\Core\Providers\RateLimiter;
use Leantime\Core\Providers\Redis;
use Leantime\Core\Providers\Session;
use Leantime\Core\Providers\TemplateServiceProvider;
use Leantime\Core\Providers\Views;

return [
    'app' => [
        'providers' => [
            /*
             * Package Service Providers...
             */
            Barryvdh\Debugbar\ServiceProvider::class,

            /*
             * Application Service Providers...
             */
            \Leantime\Core\Providers\AppServiceProvider::class,
            \Leantime\Core\Providers\Redis::class,
            \Leantime\Core\Providers\Cache::class, //\Illuminate\Cache\CacheServiceProvider::class,

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


            \Leantime\Core\Providers\Session::class,

            //\Illuminate\Redis\RedisServiceProvider::class,
            //\Illuminate\Session\SessionServiceProvider::class,
            //\Illuminate\Translation\TranslationServiceProvider::class,
            \Illuminate\Validation\ValidationServiceProvider::class,
            //\Illuminate\View\ViewServiceProvider::class,

            \Leantime\Core\Providers\Auth::class,
            \Leantime\Core\Providers\RateLimiter::class,
            \Leantime\Core\Providers\Db::class,
            \Leantime\Core\Providers\Language::class,
            \Leantime\Core\Providers\RouteServiceProvider::class,

            \Leantime\Core\Providers\Frontcontroller::class,
            \Leantime\Core\Providers\Views::class,
            \Leantime\Core\Providers\TemplateServiceProvider::class,

        ],
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
            'single' => [
                'driver' => 'single',
                'path' => storage_path('logs/leantime.log'),
                'permission' => 0664,
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
        ],
        'default' => 'single',
    ],
    "debugbar" => [

        /*
         |--------------------------------------------------------------------------
         | Debugbar Settings
         |--------------------------------------------------------------------------
         |
         | Debugbar is enabled by default, when debug is set to true in app.php.
         | You can override the value by setting enable to true or false instead of null.
         |
         | You can provide an array of URI's that must be ignored (eg. 'api/*')
         |
         */

        'enabled' => null,
        'except' => [
            'telescope*',
            'horizon*',
        ],

        /*
         |--------------------------------------------------------------------------
         | Storage settings
         |--------------------------------------------------------------------------
         |
         | DebugBar stores data for session/ajax requests.
         | You can disable this, so the debugbar stores data in headers/session,
         | but this can cause problems with large data collectors.
         | By default, file storage (in the storage folder) is used. Redis and PDO
         | can also be used. For PDO, run the package migrations first.
         |
         | Warning: Enabling storage.open will allow everyone to access previous
         | request, do not enable open storage in publicly available environments!
         | Specify a callback if you want to limit based on IP or authentication.
         | Leaving it to null will allow localhost only.
         */
        'storage' => [
            'enabled'    => true,
            'open'       => true, // bool/callback.
            'driver'     => 'file', // redis, file, pdo, socket, custom
            'path'       => storage_path('debugbar'), // For file driver
            'connection' => null,   // Leave null for default connection (Redis/PDO)
            'provider'   => '', // Instance of StorageInterface for custom driver
            'hostname'   => '127.0.0.1', // Hostname to use with the "socket" driver
            'port'       => 2304, // Port to use with the "socket" driver
        ],

        /*
        |--------------------------------------------------------------------------
        | Editor
        |--------------------------------------------------------------------------
        |
        | Choose your preferred editor to use when clicking file name.
        |
        | Supported: "phpstorm", "vscode", "vscode-insiders", "vscode-remote",
        |            "vscode-insiders-remote", "vscodium", "textmate", "emacs",
        |            "sublime", "atom", "nova", "macvim", "idea", "netbeans",
        |            "xdebug", "espresso"
        |
        */

        'editor' => 'phpstorm',

          /*
         |--------------------------------------------------------------------------
         | Vendors
         |--------------------------------------------------------------------------
         |
         | Vendor files are included by default, but can be set to false.
         | This can also be set to 'js' or 'css', to only include javascript or css vendor files.
         | Vendor files are for css: font-awesome (including fonts) and highlight.js (css files)
         | and for js: jquery and highlight.js
         | So if you want syntax highlighting, set it to true.
         | jQuery is set to not conflict with existing jQuery scripts.
         |
         */

        'include_vendors' => true,

        /*
         |--------------------------------------------------------------------------
         | Capture Ajax Requests
         |--------------------------------------------------------------------------
         |
         | The Debugbar can capture Ajax requests and display them. If you don't want this (ie. because of errors),
         | you can use this option to disable sending the data through the headers.
         |
         | Optionally, you can also send ServerTiming headers on ajax requests for the Chrome DevTools.
         |
         | Note for your request to be identified as ajax requests they must either send the header
         | X-Requested-With with the value XMLHttpRequest (most JS libraries send this), or have application/json as a Accept header.
         |
         | By default `ajax_handler_auto_show` is set to true allowing ajax requests to be shown automatically in the Debugbar.
         | Changing `ajax_handler_auto_show` to false will prevent the Debugbar from reloading.
         */

        'capture_ajax' => true,
        'add_ajax_timing' => true,
        'ajax_handler_auto_show' => true,
        'ajax_handler_enable_tab' => true,

        /*
         |--------------------------------------------------------------------------
         | Custom Error Handler for Deprecated warnings
         |--------------------------------------------------------------------------
         |
         | When enabled, the Debugbar shows deprecated warnings for Symfony components
         | in the Messages tab.
         |
         */
        'error_handler' => false,

        /*
         |--------------------------------------------------------------------------
         | Clockwork integration
         |--------------------------------------------------------------------------
         |
         | The Debugbar can emulate the Clockwork headers, so you can use the Chrome
         | Extension, without the server-side code. It uses Debugbar collectors instead.
         |
         */
        'clockwork' => false,

        /*
         |--------------------------------------------------------------------------
         | DataCollectors
         |--------------------------------------------------------------------------
         |
         | Enable/disable DataCollectors
         |
         */

        'collectors' => [
            'phpinfo'         => true,  // Php version
            'messages'        => true,  // Messages
            'time'            => true,  // Time Datalogger
            'memory'          => true,  // Memory usage
            'exceptions'      => true,  // Exception displayer
            'log'             => true,  // Logs from Monolog (merged in messages if enabled)
            'db'              => false,  // Show database (PDO) queries and bindings
            'views'           => true,  // Views with their data
            'route'           => true,  // Current route information
            'auth'            => false, // Display Laravel authentication status
            'gate'            => false,  // Display Laravel Gate checks
            'session'         => true,  // Display session data
            'symfony_request' => true,  // Only one can be enabled..
            'mail'            => false,  // Catch mail messages
            'laravel'         => false, // Laravel version and environment
            'events'          => true, // All events fired
            'default_request' => false, // Regular or special Symfony request logger
            'logs'            => true, // Add the latest log messages
            'files'           => true, // Show the included files
            'config'          => true, // Display config settings
            'cache'           => true, // Display cache events
            'models'          => false,  // Display models
            'livewire'        => false,  // Display Livewire (when available)
            'jobs'            => false, // Display dispatched jobs
        ],

        /*
         |--------------------------------------------------------------------------
         | Extra options
         |--------------------------------------------------------------------------
         |
         | Configure some DataCollectors
         |
         */

        'options' => [
            'time' => [
                'memory_usage' => false,  // Calculated by subtracting memory start and end, it may be inaccurate
            ],
            'messages' => [
                'trace' => true,   // Trace the origin of the debug message
            ],
            'memory' => [
                'reset_peak' => false,     // run memory_reset_peak_usage before collecting
                'with_baseline' => false,  // Set boot memory usage as memory peak baseline
                'precision' => 0,          // Memory rounding precision
            ],
            'auth' => [
                'show_name' => true,   // Also show the users name/email in the debugbar
                'show_guards' => true, // Show the guards that are used
            ],
            'db' => [
                'with_params'       => true,   // Render SQL with the parameters substituted
                'backtrace'         => true,   // Use a backtrace to find the origin of the query in your files.
                'backtrace_exclude_paths' => [],   // Paths to exclude from backtrace. (in addition to defaults)
                'timeline'          => false,  // Add the queries to the timeline
                'duration_background'  => true,   // Show shaded background on each query relative to how long it took to execute.
                'explain' => [                 // Show EXPLAIN output on queries
                    'enabled' => false,
                    'types' => ['SELECT'],     // Deprecated setting, is always only SELECT
                ],
                'hints'             => false,    // Show hints for common mistakes
                'show_copy'         => false,    // Show copy button next to the query,
                'slow_threshold'    => false,   // Only track queries that last longer than this time in ms
                'memory_usage'      => false,   // Show queries memory usage
                'soft_limit'       => 100,      // After the soft limit, no parameters/backtrace are captured
                'hard_limit'       => 500,      // After the hard limit, queries are ignored
            ],
            'mail' => [
                'timeline' => false,  // Add mails to the timeline
                'show_body' => true,
            ],
            'views' => [
                'timeline' => true,    // Add the views to the timeline (Experimental)
                'data' => true,        //true for all data, 'keys' for only names, false for no parameters.
                'group' => 50,          // Group duplicate views. Pass value to auto-group, or true/false to force
                'exclude_paths' => [    // Add the paths which you don't want to appear in the views
                    'vendor/filament'   // Exclude Filament components by default
                ],
            ],
            'route' => [
                'label' => true,  // show complete route on bar
            ],
            'session' => [
                'hiddens' => [], // hides sensitive values using array paths
            ],
            'symfony_request' => [
                'hiddens' => [], // hides sensitive values using array paths, example: request_request.password
            ],
            'events' => [
                'data' => true, // collect events data, listeners
            ],
            'logs' => [
                'file' => null,
            ],
            'cache' => [
                'values' => false, // collect cache values
            ],
        ],

        /*
         |--------------------------------------------------------------------------
         | Inject Debugbar in Response
         |--------------------------------------------------------------------------
         |
         | Usually, the debugbar is added just before </body>, by listening to the
         | Response after the App is done. If you disable this, you have to add them
         | in your template yourself. See http://phpdebugbar.com/docs/rendering.html
         |
         */

        'inject' => true,

        /*
         |--------------------------------------------------------------------------
         | DebugBar route prefix
         |--------------------------------------------------------------------------
         |
         | Sometimes you want to set route prefix to be used by DebugBar to load
         | its resources from. Usually the need comes from misconfigured web server or
         | from trying to overcome bugs like this: http://trac.nginx.org/nginx/ticket/97
         |
         */
        'route_prefix' => '_debugbar',

        /*
         |--------------------------------------------------------------------------
         | DebugBar route middleware
         |--------------------------------------------------------------------------
         |
         | Additional middleware to run on the Debugbar routes
         */
        'route_middleware' => [],

        /*
         |--------------------------------------------------------------------------
         | DebugBar route domain
         |--------------------------------------------------------------------------
         |
         | By default DebugBar route served from the same domain that request served.
         | To override default domain, specify it as a non-empty value.
         */
        'route_domain' => null,

        /*
         |--------------------------------------------------------------------------
         | DebugBar theme
         |--------------------------------------------------------------------------
         |
         | Switches between light and dark theme. If set to auto it will respect system preferences
         | Possible values: auto, light, dark
         */
        'theme' => env('DEBUGBAR_THEME', 'auto'),

        /*
         |--------------------------------------------------------------------------
         | Backtrace stack limit
         |--------------------------------------------------------------------------
         |
         | By default, the DebugBar limits the number of frames returned by the 'debug_backtrace()' function.
         | If you need larger stacktraces, you can increase this number. Setting it to 0 will result in no limit.
         */
        'debug_backtrace_limit' => 50,
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
                'connection' => 'default',
                'path' => storage_path('framework/cache/installation/data'),
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

        'lifetime' => 28800,

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
        | Session Database Connection
        |--------------------------------------------------------------------------
        |
        | When using the "database" or "redis" session drivers, you may specify a
        | connection that should be used to manage these sessions. This should
        | correspond to a connection in your database configuration options.
        |
        */

        'connection' => 'session',

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

        'store' => 'installation',

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

        'domain' => env('SESSION_DOMAIN'),

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

        'secure' => false,

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
];
