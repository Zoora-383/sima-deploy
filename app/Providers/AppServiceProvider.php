<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

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
        if (config('app.env') === 'production' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::define('viewApiDocs', function ($user = null) {
            return true;
        });

        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'App\Models\Item' => \App\Models\Item::class,
            'App\Models\MaintenanceRequest' => \App\Models\MaintenanceRequest::class,
            'App\Models\SPK' => \App\Models\SPK::class,
            'item' => \App\Models\Item::class,
            'maintenance' => \App\Models\MaintenanceRequest::class,
            'spk' => \App\Models\SPK::class,
        ]);

        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer', 'JWT')
                );
            });

        RateLimiter::for('change-password', function (Request $request) {
            $user = $request->user();
            $key = $user ? $user->id . '|' . $request->ip() : $request->ip();
            return Limit::perMinute(5)->by($key);
        });
    }
}
