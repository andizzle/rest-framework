<?php

namespace Andizzle\Rest\Serializers;

use REST;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;


class Serializer implements SerializerInterface {

    protected $merges = array();

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
    public function serialize(ArrayableInterface $instance, $root, $withRelations = true) {

        $relationship = array();

        if( $this->isCollection($instance) ) {

            $root = str_plural($root);
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

            $side_loads = $instance->getSideLoads();

            // Set visible relations to hidden
            if( !$withRelations )
                $instance->setHidden(array_merge($instance->getHidden(), $instance->getSideLoads()));

            $instance->load($instance->getWith());

        }

        if( $withRelations ) {
            $relationship = $this->serializeRelations($instance);
            $instance = $this->serializeKeys($instance);
        }

        $result = array(
            $root => $instance->toArray()
        );
        return REST::camelCaseInput(array_merge($result, $relationship));

    }

    /**
     * Serialize all toManys to keys
     *
     * @param ArrayableInterface $instance
     * @return $instance
     */
    public function serializeKeys(ArrayableInterface $instance) {

        $is_collection = True;
        $result = new Collection;
        if( !$this->isCollection($instance) ) {
            $is_collection = False;
            $collection = new Collection;
            $instance = $collection->add($instance);
        }

        $instance->transform(function($item)
        {

            $side_loads = $item->getSideLoads();

            foreach($side_loads as $load) {

                if($item->{$load} instanceof Collection)
                    // If is a collection then the result is a list of
                    // id. e.g: [1, 2, 3]
                    $item->setRelation($load, Collection::make($item->{$load}->take(Config::get('api.sideloads_limit'))->modelKeys()));

                else
                    // otherwise the result is an id. e.g: 2
                    if( $value = $item->{$load} ) {
                        $item->__unset($load);
                        $item->setAttribute($load, $value->getKey());
                    }

            }

            return $item;

        });

        if( !$is_collection )
            return $instance->pop();

        return $instance;

    }

    /**
     * Serialize intance's relations.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $instance
     * @return array
     */
    public function serializeRelations(ArrayableInterface $instance) {

        $result = new Collection;

        if( !$this->isCollection($instance) ) {
            $collection = new Collection;
            $instance = $collection->add($instance);
        }

        $sub_result = $this->collectRelations($instance);
        $sub_result = $this->mergeRelations($sub_result);
        foreach($sub_result as $id => $value) {
            $result->put($id, $value);
        }
        return $result->toArray();

    }

    /**
     * Collect relations from a collections. This will group the
     * comment relations to new collection. For example:
     * person1: {jobs: []}, person2: {jobs:[]}
     * ==> person1, person2 :: {jobs: []}
     *
     * @param \Illuminate\Database\Eloquent\Collection $instance
     * @return array
     */
    public function collectRelations(Collection $instance) {

        $sub_result = array();
        // This is the magic function where we process all
        // relationships. If the relatinship is a model, change it to
        // collection. If more than one model-relationship exists,
        // they are all added to collection and filtered.
        $instance->each(function($item) use (&$sub_result)
        {

            $serializables = $item->getSideLoads();

            foreach( $serializables as $serializable ) {

                $key = $serializable;
                if(!in_array($key, array('people', 'men', 'women', 'children')))
                    $key = str_plural($key);

                $item_relation = $item->getRelation($serializable);
                if( $item_relation instanceof Collection )
                    $item_relation = $item_relation->take(Config::get('api.sideloads_limit'));

                if( !$this->isEmptyOrNull($item_relation) ) {

                    if( !$this->isCollection($item_relation) ) {
                        $rel = new Collection;
                        $item_relation = $rel->add($item_relation);
                    }

                    if( array_key_exists($key, $sub_result) )
                        $sub_result[$key] = $sub_result[$key]->merge($item_relation)->unique();
                    else
                        $sub_result[$key] = $item_relation;

                }
            }

        });

        return $sub_result;

    }

    /**
     * Merge collections to put same type of collections together.
     *
     * @param array $result
     * @return array
     */
    public function mergeRelations(array $result) {

        foreach( $this->merges as $key => $merge_to ) {
            if( isset($result[$key]) ) {

                $relation = array_pull($result, $key);

                if( !$relation->isEmpty() ) {
                    if( !isset($result[$merge_to]) )
                        $result[$merge_to] = $relation;
                    else
                        $result[$merge_to] = $result[$merge_to]->merge($relation)->unique();
                }

            }
        }

        return $result;

    }


}