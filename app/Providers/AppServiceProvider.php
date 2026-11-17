<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Helpers\UrlHelper;
use App\Http\Controllers\FinancialExportController;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

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

        RateLimiter::for('api-data', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('admin.finance.partials.export-dropdown', function ($view) {
            $view->with('exportTypes', FinancialExportController::availableTypes())
                ->with('exportFields', FinancialExportController::fieldDefs())
                ->with('exportIsAdmin', FinancialExportController::isAdmin());
        });
    }
}
