<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;

class Mix
{
    use DispatchesEvents;

    private $manifest = [];

    public function __construct()
    {
        // if (! Cache::store('installation')->has('manifest://' . ($manifestDir = APP_ROOT . '/public/dist'))) {
        //     Cache::store('installation')->put('manifest://' . $manifestDir, json_decode(file_get_contents("$manifestDir/mix-manifest.json"), true), 60 * 60 * 24 * 7);
        // }
        // $this->manifest[$manifestDir] = Cache::store('installation')->get('manifest://' . $manifestDir);
        $this->manifest[$manifestDir = APP_ROOT . '/public/dist'] = json_decode(file_get_contents("$manifestDir/mix-manifest.json"), true);

        /**
         * WARNING: All files in the manifest directories will be exposed to public queries!
         *
         * @var string[] $manifestDirectories
         **/
        $manifestDirectories = self::dispatch_filter('mix_manifest_directories', []);

        if (empty($manifestDirectories) || ! is_array($manifestDirectories)) {
            return;
        }

        foreach ($manifestDirectories as $manifestDirectory) {
            $manifestDirectory = rtrim($manifestDirectory, '/');
            // if (Cache::store('installation')->has('manifest://' . $manifestDirectory)) {
            //     $this->manifest[$manifestDirectory] = Cache::get('manifest://' . $manifestDirectory);
            //     continue;
            // }

            if (! file_exists($manifestPath = $manifestDirectory . '/mix-manifest.json')) {
                continue;
            }

            // Cache::store('installation')->put('manifest://' . $manifestDirectory, array_map(
            //     fn ($path) => $this->preparePath($path, $manifestDirectory),
            //     json_decode(file_get_contents($manifestPath), true)
            // ), 60 * 60 * 24 * 7);
            //
            // $this->manifest[$manifestDirectory] = Cache::store('installation')->get('manifest://' . $manifestDirectory);

            $this->manifest[$manifestDirectory] = array_map(
                fn ($path) => $this->preparePath($path, $manifestDirectory),
                json_decode(file_get_contents($manifestPath), true)
            );
        }
    }

    public function __invoke(string $path, string $manifestDirectory = ''): string
    {
        $manifestDirectory = Str::start('/', $manifestDirectory ?: APP_ROOT . '/public/dist');
        $path = Str::start($path, '/');

        if (! isset($this->manifest[$manifestDirectory])) {
            throw new \Exception("Unable to locate Manifest in: {$manifestDirectory}.");
        }

        if (! isset($this->manifest[$manifestDirectory][$path])) {
            throw new \InvalidArgumentException("Unable to locate Mix file: {$path}.");
        }

        return $this->manifest[$manifestDirectory][$path];
    }

    private function preparePath(string $path, string $manifestDirectory): string
    {
        if (str_starts_with($manifestDirectory, APP_ROOT . '/app')) {
            $urlPrefix = Str::of($manifestDirectory)
                ->replace(APP_ROOT . '/app', '')
                ->ltrim('/')
                ->explode('/')
                ->map(fn ($pathPart) => Str::slug($pathPart))
                ->join('/');

            $urlPrefix = Str::of($urlPrefix)
                ->prepend('/api/static-asset/')
                ->rtrim('/')
                ->toString();
        } else {
            $urlPrefix = Str::of($manifestDirectory)
                ->replace(APP_ROOT . '/public', '')
                ->start('/public')
                ->rtrim('/')
                ->toString();
        }

        return $urlPrefix . Str::start($path, '/');
    }

    public function getManifest(): array
    {
        return $this->manifest;
    }
}
