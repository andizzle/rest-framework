<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class BaseSerializer extends Serializer implements SerializerInterface {

    /**
     * Serialize instance to json ready array.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $instance
     * @param string $root
     * @return array
     */
    public function serialize(Arrayable $instance, $root) {

        $relationship = array();

        $root = $this->getRoot($instance, $root);

        if( $this->isEmptyOrNull($instance) )
            return [
                $root => []
            ];

        return [
            $root => $instance->toArray()
        ];

    }

    public function getRoot(Arrayable $instance, $root) {

        return $this->isCollection($instance) ? str_plural($root) : $root;
    }

    /**
     * Convert the array keys to wanted case format.
     *
     * @param array $data
     * @return array
     */
    public function dehydrate(array $data) {

        return REST::convertCase($data);

    }

}