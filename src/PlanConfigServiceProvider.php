<?php namespace Seanstewart\PlanConfig;

use Illuminate\Support\ServiceProvider;

class PlanConfigServiceProvider extends ServiceProvider {

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/plans.php' => config_path('plans.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('planconfig', function ()
        {
            return new PlanConfig();
        });
    }
}