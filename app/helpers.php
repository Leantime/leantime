<?php

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Language;
use Leantime\Core\Support\Build;
use Leantime\Core\Support\Cast;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Core\Support\Format;
use Leantime\Core\Support\FromFormat;
use Symfony\Component\HttpFoundation\RedirectResponse;

if (! function_exists('__')) {
    /**
     * Translate a string.
     *
     * @throws BindingResolutionException
     */
    function __(string $index, string $default = ''): string
    {
        return app()->make(Language::class)->__(index: $index, default: $default);
    }
}

if (! function_exists('array_sort')) {
    /**
     * sort array of arrqays by value
     *
     * @param  string  $sortyBy
     */
    function array_sort(array $array, mixed $sortyBy): array
    {

        if (is_string($sortyBy)) {
            $collection = collect($array);

            $sorted = $collection->sortBy($sortyBy, SORT_NATURAL);

            return $sorted->values()->all();
        } else {
            return \Illuminate\Support\Collection::make($array)->sortBy($sortyBy)->all();
        }
    }
}

if (! function_exists('do_once')) {
    /**
     * Execute a callback only once.
     */
    function do_once(string $key, Closure $callback, bool $across_requests = false): void
    {
        $key = "do_once_{$key}";

        if ($across_requests) {
            if (session()->exists('do_once') === false) {
                session(['do_once' => []]);
            }

            if (session('do_once.'.$key) ?? false) {
                return;
            }

            session(['do_once.'.$key => true]);
        } else {
            static $do_once;
            $do_once ??= [];

            if ($do_once[$key] ?? false) {
                return;
            }

            $do_once[$key] = true;
        }

        $callback();
    }
}

if (! function_exists('build')) {
    /**
     * Turns any object into a builder object
     *
     **/
    function build(object $object): Build
    {
        return new Build($object);
    }
}

if (! function_exists('format')) {
    /**
     * Returns a format object to format string values
     *
     * @param  string|int|float|DateTime|Carbon|null  $value
     * @param  string|int|float|DateTime|null  $value2
     */
    function format(string|int|float|null|\DateTime|\Carbon\CarbonInterface $value, string|int|float|null|\DateTime|\Carbon\CarbonInterface $value2 = null, ?FromFormat $fromFormat = FromFormat::DbDate): Format|string
    {
        return new Format($value, $value2, $fromFormat);
    }
}

if (! function_exists('cast')) {
    /**
     * Casts a variable to a different type if possible.
     *
     * @param  mixed  $obj  The object to be cast.
     * @param  string  $to_class  The class to which the object should be cast.
     * @param  array  $construct_params  Optional parameters to pass to the constructor.
     * @param  array  $mappings  Make sure certain sub properties are casted to specific types.
     * @return mixed The casted object, or throws an exception on failure.
     *
     * @throws \InvalidArgumentException If the class does not exist.
     * @throws \RuntimeException|ReflectionException On serialization errors.
     */
    function cast(mixed $source, string $classOrType, array $constructParams = [], array $mappings = []): mixed
    {
        if (in_array($classOrType, ['int', 'integer', 'float', 'string', 'str', 'bool', 'boolean', 'object', 'stdClass', 'array'])) {
            return Cast::castSimple($source, $classOrType);
        }

        if (enum_exists($classOrType)) {
            return Cast::castEnum($source, $classOrType);
        }

        // Convert string to date if required.
        if (is_string($source) && is_a($classOrType, CarbonInterface::class, true)) {
            return Cast::castDateTime($source);
        }

        return (new Cast($source))->castTo($classOrType, $constructParams, $mappings);
    }
}

if (! function_exists('dtHelper')) {
    /**
     * Get a singleton instance of the DateTimeHelper class.
     *
     *
     * @throws BindingResolutionException
     */
    function dtHelper(): ?DateTimeHelper
    {
        if (! app()->bound(DateTimeHelper::class)) {
            app()->singleton(DateTimeHelper::class);
        }

        return app()->make(DateTimeHelper::class);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $url
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    function redirect($url = null, $http_response_code = 302, $headers = [], $secure = null)
    {
        return new RedirectResponse(
            trim(preg_replace('/\s\s+/', '', strip_tags($url))),
            $http_response_code
        );
    }
}

if (! function_exists('currentRoute')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     */
    function currentRoute()
    {

        return app('request')->getCurrentRoute();

    }
}

if (! function_exists('get_domain_key')) {

    /**
     * Gets a unique instance key determined by domain
     */
    function get_domain_key()
    {

        //Now that we know where the instance is bing called from
        //Let's add a domain level cache.

        $host = app('request')->host();

        $url = config('app.url');
        if ($url && isset($url['host'])) {
            $host = $url['host'];
        }

        $domainKeyParts = config('app.url').config('app.key');
        $slug = \Illuminate\Support\Str::slug($domainKeyParts);
        $domainCacheName = md5($slug);

        return $domainCacheName;

    }

}

if (! function_exists('mix')) {
    /**
     * Get the path to a versioned Mix file. Customized for Leantime.
     *
     * @return Mix|string
     *
     * @throws BindingResolutionException
     */
    function mix(string $path = '', string $manifestDirectory = ''): \Leantime\Core\Support\Mix|string
    {
        if (! ($app = app())->bound(\Leantime\Core\Support\Mix::class)) {
            $app->instance(\Leantime\Core\Support\Mix::class, new \Leantime\Core\Support\Mix);
        }

        $mix = $app->make(\Leantime\Core\Support\Mix::class);

        if (empty($path)) {
            return $mix;
        }

        return $mix($path, $manifestDirectory);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string  $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $url
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    function redirect($url = null, $http_response_code = 302, $headers = [], $secure = null)
    {
        return new RedirectResponse(
            trim(preg_replace('/\s\s+/', '', strip_tags($url))),
            $http_response_code
        );
    }
}

if (! function_exists('currentRoute')) {
    /**
     * Get an instance of the redirector.
     *
     * @param  string|null  $to
     * @param  int  $status
     * @param  array  $headers
     * @param  bool|null  $secure
     */
    function currentRoute()
    {

        return app('request')->getCurrentRoute();

    }
}

if (! function_exists('get_release_version')) {

    /**
     * Gets a unique instance key determined by domain
     */
    function get_release_version()
    {

        $appSettings = app()->make(\Leantime\Core\Configuration\AppSettings::class);

        return $appSettings->appVersion;

    }

}
