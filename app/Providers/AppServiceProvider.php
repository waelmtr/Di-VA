<?php

namespace App\Providers;

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

        //else register your services you require for production
        // $this->app['request']->server->set('HTTPS', true);
        if ($this->app->environment() != 'local') {
            $this->app['request']->server->set('HTTPS', true);
        }
    }


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
    }
}