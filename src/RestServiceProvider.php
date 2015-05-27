<?php namespace Andizzle\Rest;

use Andizzle\Rest\RestServer;
use Illuminate\Support\ServiceProvider;


class RestServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {

        $this->app->singleton('rest.server', function ($app)
        {
            return new RestServer;
        });

        $this->app->singleton('rest.serializer', function ($app)
        {
            $model = $app['config']->get('rest.serializer.model');
            return new $model;
        });
    }

    /**
     * Bootstrap the configuration
     *
     * @return void
     */
    public function boot() {

        $config = realpath(__DIR__ . '/../config/config.php');

        $this->mergeConfigFrom($config, 'rest');

        $this->publishes([$config => config_path('rest.php')], 'config');

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['rest.server', 'rest.serializer'];
    }

}
