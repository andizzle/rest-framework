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

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {

        $this->app->singleton('Andizzle\Rest\RestServer', function ($app)
        {
            $case = $app['config']['andizzle/rest-framework::case'];
            return new RestServer($case);
        });

        // $this->app->singleton('Andizzle\Rest\RestServer', function ($app)
        // {
        //     $model = $app['config']['andizzle/rest-framework::serializer.model'];
        //     return new $model;
        // });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {

        return ['Andizzle\Rest\RestServer'];

    }

}
