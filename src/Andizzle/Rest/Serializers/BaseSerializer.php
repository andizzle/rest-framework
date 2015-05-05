<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class BaseSerializer extends Serializer implements SerializerInterface {

    protected $with_relations = true;

    /**
     * Get the with_relations attribute.
     *
     * @return boolean
     */
    public function getWithRelations() {

        return $this->with_relations;

    }

    /**
     * Set the with_relations attribtue
     *
     * @param boolean $with_relations
     * @return Andizzle\Rest\Serializers\BaseSerializer
     */
    public function setWithRelations($with_relations) {

        $this->with_relations = $with_relations;
        return $this;

    }

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

        if( $this->isCollection($instance) ) {

            if( $this->isEmptyOrNull($instance) )
                return array(
                    $root => array()
                );

            // Set visible relations to hidden
            $instance->transform(function($item)
            {

                if( !$this->with_relations )
                    $item->setHidden(array_merge($item->getHidden(), $item->getSideLoads()));

                return $item;

            });

        } else {


            $instance->load(array_merge($instance->getWith(), $instance->getSideLoads()));

            // Set visible relations to hidden
            if( !$this->with_relations )
                $instance->setHidden(array_merge($instance->getHidden(), $instance->getSideLoads()));

        }

        return array(
            $root => $instance->toArray()
        );

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