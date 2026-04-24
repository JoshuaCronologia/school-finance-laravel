<?php

namespace App\Providers;

use App\Services\CacheService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Force HTTPS in production (Vercel proxy sends requests as HTTP internally)
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');

            if ($vercelUrl = env('VERCEL_URL')) {
                $url = 'https://' . $vercelUrl;
                config(['app.url' => $url]);
                config(['app.asset_url' => $url]);
            }
        }

        // SSO Gate bridge: allow @can() directives to work for SSO session users
        Gate::before(function ($user = null, $ability = null) {
            if ($user === null && Session::has('is_sso')) {
                $ssoPermissions = Session::get('permissions', []);

                if (in_array($ability, $ssoPermissions)) {
                    return true;
                }

                $map = config('acl.permission_map', []);
                foreach ($ssoPermissions as $ssoPerm) {
                    if (isset($map[$ssoPerm]) && in_array($ability, $map[$ssoPerm])) {
                        return true;
                    }
                }

                return null;
            }
        });

        // Currency formatting Blade directive
        Blade::directive('currency', function ($expression) {
            return "<?php echo '&#8369;' . number_format($expression, 2); ?>";
        });

        // Date formatting Blade directive (Philippine format)
        Blade::directive('dateformat', function ($expression) {
            return "<?php echo optional($expression)->format('M d, Y') ?? '-'; ?>";
        });

        // Status badge Blade directive
        Blade::directive('status', function ($expression) {
            return "<?php echo ucfirst(str_replace('_', ' ', $expression)); ?>";
        });

        // Observe model changes for cache invalidation
        \App\Models\Notification::observe(\App\Observers\NotificationObserver::class);
        \App\Models\FeeAccountMapping::observe(\App\Observers\FeeAccountMappingObserver::class);

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
