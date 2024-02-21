<?php

namespace Vedmant\FeedReader;

use Illuminate\Support\ServiceProvider;

class FeedReaderServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
     * Bootstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/feed-reader.php' => config_path('feed-reader.php')
        ], 'config');
    }

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        // Bind to the "Asset" section
        $this->app->bind('feed-reader', function($app) {
            return new FeedReader($app);
        });

        $this->mergeConfigFrom(
            __DIR__.'/../config/feed-reader.php', 'feed-reader'
        );
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('feed-reader');
	}
}
