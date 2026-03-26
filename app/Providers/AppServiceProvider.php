<?php

namespace App\Providers;

use App\Database\PostgresConnection;
use App\Services\CacheService;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
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
        // Use custom PostgreSQL connection that handles booleans properly
        Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
            return new PostgresConnection($connection, $database, $prefix, $config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production (Vercel proxy sends requests as HTTP internally)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');

            // Dynamically set APP_URL from Vercel's auto-provided VERCEL_URL
            if ($vercelUrl = env('VERCEL_URL')) {
                $url = 'https://' . $vercelUrl;
                config(['app.url' => $url]);
                config(['app.asset_url' => $url]);
            }
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

        // Auto-clear financial caches when key models change
        $clearCaches = function () {
            CacheService::clearFinancialCaches();
        };

        foreach ([
            \App\Models\Budget::class,
            \App\Models\ApBill::class,
            \App\Models\ArInvoice::class,
            \App\Models\JournalEntry::class,
            \App\Models\DisbursementRequest::class,
            \App\Models\DisbursementPayment::class,
            \App\Models\ArCollection::class,
            \App\Models\ApPayment::class,
        ] as $model) {
            $model::saved($clearCaches);
            $model::deleted($clearCaches);
        }
    }
}
