<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Password defaults
        Password::defaults(function () {
            return $this->app->environment('production')
                ? Password::min(8)->mixedCase()->numbers()->uncompromised()
                : Password::min(8);
        });

        // Currency formatting Blade directive
        Blade::directive('currency', function ($expression) {
            return "<?php echo '&#8369;' . number_format($expression, 2); ?>";
        });

        // Date formatting Blade directive (Philippine format)
        Blade::directive('dateformat', function ($expression) {
            return "<?php echo ($expression)?->format('M d, Y') ?? '-'; ?>";
        });

        // Status badge Blade directive
        Blade::directive('status', function ($expression) {
            return "<?php echo ucfirst(str_replace('_', ' ', $expression)); ?>";
        });
    }
}
