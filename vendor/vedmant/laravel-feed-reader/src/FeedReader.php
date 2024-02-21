<?php

namespace Vedmant\FeedReader;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Psr\SimpleCache\CacheInterface;
use SimplePie\SimplePie;

class FeedReader
{
    /**
     * @var Container
     */
    private $app;

    /**
     * FeedReader constructor.
     *
     * @param Container $app
     */
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * Used to parse an RSS feed.
     *
     * @param        $url
     * @param string $configuration
     * @param array $options
     * @return SimplePie
     *
     * @throws BindingResolutionException
     */
    public function read($url, $configuration = 'default', array $options = [])
    {
        // Set up the object
        $sp = $this->app->make(SimplePie::class);

        $cache = Cache::store($this->readConfig($configuration, 'cache.driver', 'file'));
        // Configure it
        if ($cache instanceof CacheInterface)
        {
            // Enable caching
            $sp->enable_cache();
            $sp->set_cache($cache);
            $sp->set_cache_duration($this->readConfig($configuration, 'cache.duration', 3600));
        }
        else
        {
            // Disable caching
            $sp->enable_cache(false);
        }

        // Whether to force the feed reading
        $sp->force_feed($this->readConfig($configuration, 'force-feed', false));

        // Should we be ordering the feed by date?
        $sp->enable_order_by_date($this->readConfig($configuration, 'order-by-date', false));

        if (! $this->readConfig($configuration, 'ssl-verify', true)) {
            $sp->set_curl_options([
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
        }

        // If the user passes manual curl options, let's add them
        if (isset($options['curl_options'])) {
            $sp->set_curl_options($options['curl_options']);
        }

        // Set the feed URL
        $sp->set_feed_url($url);

        // Grab it
        $sp->init();

        // We are done, so return it
        return $sp;
    }

    /**
     * Used internally in order to retrieve a specific value from the configuration
     * file.
     *
     * @param string $configuration The name of the configuration to use
     * @param string $name The name of the value in the configuration file to retrieve
     * @param mixed $default The default value
     * @return mixed
     */
    private function readConfig($configuration, $name, $default)
    {
        return Config::get('feed-reader.profiles.' . $configuration . '.' . $name, $default);
    }
}
