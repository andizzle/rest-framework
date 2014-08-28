<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Contracts\ArrayableInterface;


abstract class Serializer implements SerializerInterface {

    /**
     * Check if an object is a collection;
     *
     * @param $instance
     * @return boolean
     */
    public function isCollection($instance) {

        return $instance instanceof Collection;

    }

    /**
     * Check if an object is empty or null
     *
     * @param $instance
     * @return boolean
     */
    public function isEmptyOrNull($instance) {

        return ($this->isCollection($instance) && $instance->isEmpty()) ||
            (!$this->isCollection($instance) && is_null($instance));

    }

    /**
     * Serialize instance to json ready array.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $instance
     * @param boolean $withRelations
     * @return array
     */
    public function serialize(ArrayableInterface $instance, $root, $withRelations = true) {}

    /**
     * Dehydrate the result, do any additional action you need before
     * send out the final output.
     *
     * @param array $data
     * return array
     */
    public function dehydrate(array $data) {}

}