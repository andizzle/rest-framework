<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class JSONSerializer extends BaseSerializer {

    protected $merges = array();
    protected $sideload_limit = 0;

    public function __construct() {

        $this->embed_relations = Config::get('andizzle/rest-framework::serializer.embed-relations');

    }

    /**
     * Get the merges attribute.
     *
     * @return array
     */
    public function getMerges() {

        return $this->with_relations;

    }

    /**
     * Set the merges attribtue
     *
     * @param boolean $merges
     * @return Andizzle\Rest\Serializers\BaseSerializer
     */
    public function setMerges($merges) {

        $this->merges = $merges;
        return $this;

    }

    /**
     * Serialize instance to json ready array.
     *
     * @param \Illuminate\Support\Contracts\Arrayable $instance
     * @param string $root
     * @return array
     */
    public function serialize(Arrayable $instance, $root, $limit = null) {

        $relationship = array();

        $serialized_data = parent::serialize($instance, $root);
        $root = $this->getRoot($instance, $root);

        if( $this->with_relations ) {

            if( $this->embed_relations )
                $relationship = $this->serializeRelations($instance);

            $serialized_data[$root] = $this->serializeKeys($instance)->toArray();

        }

        return array_merge($serialized_data, $relationship);

    }

    /**
     * Serialize all toManys to keys
     *
     * @param Arrayable $instance
     * @return $instance
     */
    public function serializeKeys(Arrayable $instance) {

        $is_collection = True;
        $result = new Collection;
        if( !$this->isCollection($instance) ) {
            $is_collection = False;
            $instance = \Illuminate\Database\Eloquent\Collection::make([$instance]);
        }

        $instance->transform(function($item)
        {
            // clean up current relations;
            $side_loads = $item->getSideLoads();
            foreach($item->newFromBuilder()->getSideLoads() as $relation) {

                if(!in_array($relation, $side_loads))
                    $item->__unset($relation);

            }

            foreach($side_loads as $load) {

                $relation = $item->{$load};

                if(!$this->isEmptyOrNull($relation)) {

                    $item->__unset($load);
                    if($relation instanceof Collection)
                        // If is a collection then the result is a list of
                        // id. e.g: [1, 2, 3]
                        $item->setRelation($load, Collection::make($relation->unique()->modelKeys()));
                    else
                        // otherwise the result is an id. e.g: 2
                        $item->setAttribute($load, $relation->getKey());

                } else {

                    $item->setHidden(array_merge($item->getHidden(), [$load]));

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
     * @param \Illuminate\Support\Contracts\Arrayable $instance
     * @return array
     */
    public function serializeRelations(Arrayable $instance) {

        $result = new Collection;

        if( !$this->isCollection($instance) )
            $instance = Collection::make([$instance]);

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
    private function collectRelations(Collection $instance) {

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

                    if( !$this->isCollection($item_relation) )
                        $item_relation = Collection::make($item_relation);
                    else
                        $item_relation = $item_relation->unique();

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
    private function mergeRelations(array $result) {

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