<?php

namespace App\Providers;

use App\Models\PiggyBank;
use App\Policies\PiggyBankPolicy;
use App\Services\LinkPreviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LinkPreviewService::class, function ($app) {
           return new LinkPreviewService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        // 🔧 System boot log for debugging
//        \Log::emergency('VERIFY EMAIL DEBUG: Boot method executed');
//        \Log::emergency('VERIFY EMAIL DEBUG: APP ENV = ' . app()->environment());

        // 🔐 Authorization policies
        Gate::policy(PiggyBank::class, PiggyBankPolicy::class);

        // 🌐 Set locale if user has one
        Auth::user()?->language && App::setLocale(Auth::user()->language);

        // 🧪 Set test time if session override exists (local only)
        if (app()->environment('local') && session()->has('test_date')) {
            Carbon::setTestNow(Carbon::parse(session('test_date')));
        }

        // 🌍 Force HTTPS on production and staging
        if ($this->app->environment(['production', 'staging'])) {
            URL::forceScheme('https');
        }

        // 🐛 Debug macro for inspecting signed URLs
        URL::macro('debugSignedRoute', function ($name, $parameters = [], $expiration = null, $absolute = true) {
            $temporarySignedURL = URL::temporarySignedRoute($name, $expiration, $parameters, $absolute);
            \Log::debug('Signed URL debug', [
                'url' => $temporarySignedURL,
                'name' => $name,
                'parameters' => $parameters,
                'expiration' => $expiration
            ]);
            return $temporarySignedURL;
        });

        // 🔐 Log info about URL signing key (for signature validation debugging)
        URL::setKeyResolver(function () {
            $key = config('app.key');
            \Log::debug('URL signing key info', [
                'key_length' => strlen($key),
                'key_prefix' => substr($key, 0, 7)
            ]);
            return $key;
        });
    }

}
