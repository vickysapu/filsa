<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\UrlHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    Blade::directive('routeToken', function ($expression) {
        return "<?php echo \App\Helpers\UrlHelper::routeWithToken({$expression}); ?>";
    });
    
    Blade::directive('urlToken', function ($expression) {
        return "<?php echo \App\Helpers\UrlHelper::urlWithToken({$expression}); ?>";
    });
    }
}
