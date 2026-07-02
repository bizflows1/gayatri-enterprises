<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (!$this->app->runningInConsole()) {
            $host = request()->getHost();
            if($this->app->environment('production') && $host !== 'localhost' && $host !== '127.0.0.1') {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }
    }
}
