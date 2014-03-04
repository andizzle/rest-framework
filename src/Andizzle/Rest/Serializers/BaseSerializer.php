<?php

namespace Andizzle\Rest\Serializers;

use REST;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;


class BaseSerializer extends Serializer implements SerializerInterface {

    /**
     * Serialize instance to json ready array.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $instance
     * @param boolean $withRelations
     * @return array
     */
    public function serialize(ArrayableInterface $instance, $root, $withRelations = true) {

        $relationship = array();

        $root = $this->getRoot($instance, $root);

        if( $this->isCollection($instance) ) {

            if( $this->isEmptyOrNull($instance) )
                return array(
                    $root => array()
                );

            // Set visible relations to hidden
            $instance->transform(function($item) use ($withRelations)
            {

                if( !$withRelations )
                    $item->setHidden(array_merge($item->getHidden(), $item->getSideLoads()));

                return $item->load($item->getSideLoads());

            });

        } else {


            $instance->load(array_merge($instance->getWith(), $instance->getSideLoads()));

            // Set visible relations to hidden
            if( !$withRelations )
                $instance->setHidden(array_merge($instance->getHidden(), $instance->getSideLoads()));

        }

        return array(
            $root => $instance->toArray()
        );

    }

    public function getRoot(ArrayableInterface $instance, $root) {

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