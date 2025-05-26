<?php

namespace App\Providers;

use App\Models\PiggyBank;
use App\Policies\PiggyBankPolicy;
use App\Services\LinkPreviewService;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
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
            return new LinkPreviewService;
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
                'expiration' => $expiration,
            ]);

            return $temporarySignedURL;
        });

        // 🔐 Log info about URL signing key (for signature validation debugging)
        URL::setKeyResolver(function () {
            $key = config('app.key');
            \Log::debug('URL signing key info', [
                'key_length' => strlen($key),
                'key_prefix' => substr($key, 0, 7),
            ]);

            return $key;
        });

        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return url("/reset-password/{$token}").
                '?email='.urlencode($notifiable->getEmailForPasswordReset()).
                '&lang='.urlencode($notifiable->language ?? 'en');
        });

        // 🌐 Register custom route helpers as global functions
        if (! function_exists('localizedRoute')) {
            function localizedRoute(string $routeName, array $parameters = [], ?string $locale = null): string
            {
                return \App\Helpers\RouteHelper::localizedRoute($routeName, $parameters, $locale);
            }
        }

        if (! function_exists('localizedUrl')) {
            function localizedUrl(string $routeName, array $parameters = [], ?string $locale = null): string
            {
                return \App\Helpers\RouteHelper::localizedUrl($routeName, $parameters, $locale);
            }
        }

        // 🌐 Register route macros for localized routing
        Route::macro('localizedGet', function (string $routeKey, $action) {
            return new class($routeKey, $action, 'get')
            {
                private string $routeKey;

                private $action;

                private string $method;

                private array $options = [];

                public function __construct(string $routeKey, $action, string $method)
                {
                    $this->routeKey = $routeKey;
                    $this->action = $action;
                    $this->method = $method;
                }

                public function name(string $name)
                {
                    $this->options['name'] = $name;

                    return $this;
                }

                public function middleware($middleware)
                {
                    $this->options['middleware'] = $middleware;

                    return $this;
                }

                public function where(array $constraints)
                {
                    $this->options['where'] = $constraints;

                    return $this;
                }

                public function __destruct()
                {
                    \App\Services\LocalizedRouteService::register(
                        $this->method,
                        $this->routeKey,
                        $this->action,
                        $this->options['name'] ?? 'unnamed.route',
                        $this->options
                    );
                }
            };
        });

        Route::macro('localizedPost', function (string $routeKey, $action) {
            return new class($routeKey, $action, 'post')
            {
                private string $routeKey;

                private $action;

                private string $method;

                private array $options = [];

                public function __construct(string $routeKey, $action, string $method)
                {
                    $this->routeKey = $routeKey;
                    $this->action = $action;
                    $this->method = $method;
                }

                public function name(string $name)
                {
                    $this->options['name'] = $name;

                    return $this;
                }

                public function middleware($middleware)
                {
                    $this->options['middleware'] = $middleware;

                    return $this;
                }

                public function where(array $constraints)
                {
                    $this->options['where'] = $constraints;

                    return $this;
                }

                public function __destruct()
                {
                    \App\Services\LocalizedRouteService::register(
                        $this->method,
                        $this->routeKey,
                        $this->action,
                        $this->options['name'] ?? 'unnamed.route',
                        $this->options
                    );
                }
            };
        });

    }
}
