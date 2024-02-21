<?php

return [

    /**
     * An array of the configuration profiles that the developer may want.
     *
     * @var array
     */
    'profiles' => [

        /**
         * The default configuration information
         *
         * @var array
         */
        'default' => [

            /**
             * All the cache settings
             *
             * @var array
             */
            'cache' => [

                /**
                 * How long the cache is maintained in seconds
                 *
                 * @var int
                 */
                'duration' => 3600,

                /**
                 * Whether caching is enabled.
                 *
                 * @var boolean
                 */
                'enabled' => true,

                /**
                 * The laravel cache driver used for caching
                 *
                 * @var string
                 */
                'driver' => env('CACHE_DRIVER', 'file'),
            ],

            /**
             * Whether to force the data feed to be treated as an
             * RSS feed.
             *
             * @var boolean
             */
            'force-feed' => false,

            /**
             * Whether the RSS feed should have its output ordered by date.
             *
             * @var boolean
             */
            'order-by-date' => false,

            /**
             * Whether it should verify SSL, set false to make it work with self-signed certificates
             *
             * @var boolean
             */
            'ssl-verify' => true,
        ],
    ],
];
