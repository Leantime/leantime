# Laravel Feed Reader

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](license.md)
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-actions]][link-actions]


A simple RSS feed reader for **Laravel**

## Features

 * One command to read any RSS feed
 * Different RSS feed profiles enabled

## Quick Start

To install this package run the Composer command

```
$ composer require vedmant/laravel-feed-reader
```
## Video Tutorial 
 [<img src="https://img.youtube.com/vi/Qvnf0kQyJTU/0.jpg" width="350" >](https://youtu.be/Qvnf0kQyJTU)
 
For Laravel 5.5 and above this package supports [Laravel Auto-Discovery](https://laravel.com/docs/master/packages#package-discovery) and will be discovered automatically.


For Laravel versions prior to 5.5 follow next guide:

In your `config/app.php` add following:

```php
'providers' => [

    Illuminate\Foundation\Providers\ArtisanServiceProvider::class,
    Illuminate\Auth\AuthServiceProvider::class,
    ...
    Vedmant\FeedReader\FeedReaderServiceProvider::class, // Add this line

[,

'aliases' => [

    'App'        => Illuminate\Support\Facades\App::class,
    'Artisan'    => Illuminate\Support\Facades\Artisan::class,
    ...
    'FeedReader' => Vedmant\FeedReader\Facades\FeedReader::class, // Add this line
],
```

## Setup

### Publishing the Configuration

After installing through composer, you should publish the config file.  To do this, run the following command:

```
$ php artisan vendor:publish --provider="Vedmant\FeedReader\FeedReaderServiceProvider"
```

### Configuration Values

Once published, the configuration file contains an array of profiles.  These will define how the RSS feed reader will react.  By default the "default" profile will used.  For more information on: [here](http://simplepie.org/wiki/reference/simplepie/start).

### How to use

Once you have all of the configuration settings set up, in order to read a RSS feed all you need to do is call the `read` function:

```php
$f = FeedReader::read('https://news.google.com/news/rss');

echo $f->get_title();
echo $f->get_items()[0]->get_title();
echo $f->get_items()[0]->get_content();
```

This function accepts 2 parameters however, the second parameter is optional.  The second parameter is the configuration profile that should be used when reading the RSS feed.

This will return to you the SimplePie object with the RSS feed in it.
See [SimplePie](http://simplepie.org/api/index.html) API for all available methods.

#### Passing curl options
You can also pass specific curl options per `read()` calls. You can pass these options, as an `array` as the 3rd parameter. The list of options can be found on the [PHP Manual](https://www.php.net/manual/en/function.curl-setopt.php).

Example:
```php
// You need to log in to the rss endpoint with a Digest auth
$options = [
    'curl_options' => [
        CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
        CURLOPT_USERPWD => 'username:password',
    ],
];

$f = FeedReader::read('https://news.google.com/news/rss', 'default', $options);
```

## License

Feed Reader is free software distributed under the terms of the MIT license

## Additional Information

Any issues, please [report here](https://github.com/vedmant/laravel-feed-reader/issues)

[ico-version]: https://img.shields.io/packagist/v/vedmant/laravel-feed-reader.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-downloads]: https://img.shields.io/packagist/dt/vedmant/laravel-feed-reader.svg
[ico-actions]: https://github.com/vedmant/laravel-feed-reader/actions/workflows/tests.yml/badge.svg

[link-packagist]: https://packagist.org/packages/vedmant/laravel-feed-reader
[link-downloads]: https://packagist.org/packages/vedmant/laravel-feed-reader
[link-actions]: https://github.com/vedmant/laravel-feed-reader/actions
[link-author]: https://github.com/vedmant
[link-contributors]: ../../contributors
