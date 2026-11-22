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

        // Share $isSuperAdmin ke semua view agar tidak Undefined variable
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                // Hanya set jika belum di-pass oleh controller (agar controller bisa override)
                $viewData = $view->getData();
                if (!array_key_exists('isSuperAdmin', $viewData)) {
                    $view->with('isSuperAdmin', $user->isSuperAdmin());
                }
                if (!array_key_exists('branches', $viewData)) {
                    $view->with('branches', $user->isSuperAdmin()
                        ? \App\Models\Branch::orderBy('name')->get()
                        : collect());
                }
            } else {
                $viewData = $view->getData();
                if (!array_key_exists('isSuperAdmin', $viewData)) {
                    $view->with('isSuperAdmin', false);
                }
                if (!array_key_exists('branches', $viewData)) {
                    $view->with('branches', collect());
                }
            }
        });
    }
}
