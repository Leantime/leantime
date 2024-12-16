<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Facades\File;

class AssetHelper
{
    protected static $manifestPath = '/public/dist/mix-manifest.json';
    protected static $manifest = null;

    public static function mix($path)
    {
        if (static::$manifest === null) {
            if (File::exists(base_path().static::$manifestPath)) {
                static::$manifest = json_decode(File::get(base_path().static::$manifestPath), true);
            } else {
                static::$manifest = [];
            }
        }

        if (!str_starts_with($path, '/')) {
            $path = "/{$path}";
        }

        if (isset(static::$manifest[$path])) {
            return '/dist/' . ltrim(static::$manifest[$path], '/');
        }

        return '/dist/' . ltrim($path, '/');
    }
}
