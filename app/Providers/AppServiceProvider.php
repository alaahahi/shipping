<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Model::creating(function (Model $model) {
            if (Schema::hasColumn($model->getTable(), 'uuid') && empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });

        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app->make('request');

        if ($request) {
            if ($request->isSecure()) {
                URL::forceScheme('https');
            }

            $host = $request->getHost();
            config(['session.domain' => $host]);

            $httpHost = $request->getHttpHost();
            $statefulDomains = config('sanctum.stateful', []);
            $statefulDomains[] = $host;
            $statefulDomains[] = $httpHost;
            $statefulDomains = array_values(array_unique(array_filter($statefulDomains)));
            config(['sanctum.stateful' => $statefulDomains]);

            $rootUrl = $request->getSchemeAndHttpHost();

            URL::forceRootUrl($rootUrl);
            config(['app.url' => $rootUrl]);
        }
    }
}
