<?php namespace Andizzle\Rest\Facades;

use Illuminate\Support\Facades\Facade;

class SerializerFacade extends Facade {

    /**
     * Get the registered name of the component
     *
     * @return string
     * @codeCoverageIgnore
     */
    protected static function getFacadeAccessor() {
        return 'rest.serializer';
    }
}
