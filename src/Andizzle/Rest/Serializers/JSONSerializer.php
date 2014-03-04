<?php

namespace Andizzle\Rest\Serializers;

use REST;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;


class JSONSerializer extends BaseSerializer {

    protected $merges = array();
    protected $sideload_limit = 0;

    public function __construct() {

        $this->sideload_limit = Config::get('api.sideloads_limit');

    }

    /**
     * Serialize instance to json ready array.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $instance
     * @param boolean $withRelations
     * @return array
     */
    public function serialize(ArrayableInterface $instance, $root, $withRelations = true, $limit = null) {

        $relationship = array();

        if( $limit )
            $this->sideload_limit = $limit;

        $serialized_data = parent::serialize($instance, $root, $withRelations);
        $root = $this->getRoot($instance, $root);

        if( $withRelations ) {
            $relationship = $this->serializeRelations($instance);
            $serialized_data[$root] = $this->serializeKeys($instance)->toArray();
        }

        return array_merge($serialized_data, $relationship);

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
                    $item->setRelation($load, Collection::make($item->{$load}->take($this->sideload_limit)->modelKeys()));

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

                $item_relation = $item->{$serializable};

                if( !$this->isEmptyOrNull($item_relation) ) {

                    if( !$this->isCollection($item_relation) ) {
                        $rel = new Collection;
                        $item_relation = $rel->add($item_relation);
                    } else {
                        $item_relation = $item_relation->take($this->sideload_limit);
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