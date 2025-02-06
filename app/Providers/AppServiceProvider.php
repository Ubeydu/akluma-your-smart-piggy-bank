<?php

namespace App\Providers;

use App\Models\PiggyBank;
use App\Policies\PiggyBankPolicy;
use App\Services\LinkPreviewService;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
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
        Gate::policy(PiggyBank::class, PiggyBankPolicy::class);


        if (app()->environment('local') && session()->has('test_date')) {
            Carbon::setTestNow(Carbon::parse(session('test_date')));
        }
    }
}
