<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app->make('request');

        if ($request) {
            if ($request->isSecure()) {
                URL::forceScheme('https');
            }

            $host = $request->getSchemeAndHttpHost();

            URL::forceRootUrl($host);
            config(['app.url' => $host]);
        }
    }
}
