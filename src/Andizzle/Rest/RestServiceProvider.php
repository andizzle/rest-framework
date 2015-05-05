<?php

namespace Andizzle\Rest;

use Andizzle\Rest\RestServer;
use Illuminate\Support\ServiceProvider;


class RestServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot() {

        //$this->package('andizzle/rest-framework', 'andizzle/rest-framework');

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {

        $app = $this->app;
        $app['rest.server'] = $app->share(function ($app)
        {
            $case = $app['config']['andizzle/rest-framework::case'];
            return new RestServer($case);
        });

        $app['rest.serializer'] = $app->share(function ($app)
        {
            $model = $app['config']['andizzle/rest-framework::serializer.model'];
            return new $model;
        });

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
