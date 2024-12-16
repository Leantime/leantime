<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Leantime\Core\Support\AssetHelper;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Blade::directive('mix', function ($expression) {
            return "<?php echo \Leantime\Core\Support\AssetHelper::mix($expression); ?>";
        });
    }
}
