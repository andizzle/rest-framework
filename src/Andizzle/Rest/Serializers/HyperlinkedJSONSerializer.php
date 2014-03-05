<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class HyperlinkedJSONSerializer extends BaseSerializer {

    protected $api_prefix = '';

    public function __construct() {

        $this->page_limit = Config::get('andizzle/rest-framework::page_limit');
        $this->api_prefix = REST::getApiPrefix();

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
            $this->page_limit = $limit;

        $serialized_data = parent::serialize($instance, $root, $withRelations);
        $root = $this->getRoot($instance, $root);

        if( $withRelations )
            $serialized_data[$root] = $this->serializeKeys($instance)->toArray();

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

            $links = array();
            $side_loads = $item->getSideLoads();

            foreach($side_loads as $load) {

                if($item->{$load} instanceof Collection) {
                    // we build link to ids of a model.
                    // e.g, /api/v1/books?ids=1,2,3
                    $value = $item->{$load}->take($this->page_limit);

                    if($this->isEmptyOrNull($value)) {
                        $item->__unset($load);
                        continue;
                    }

                    $root = $value->first()->getRoot();
                    $pk_field = str_plural($value->first()->getKeyName());
                    $ids = $value->modelKeys();

                } else {
                    // otherwise the result is an id. e.g: 2
                    if( $value = $item->{$load} ) {
                        $root = $value->getRoot();
                        $pk_field = $value->getKeyName();
                        $ids = array($value->getKey());
                    }

                }

                $item->__unset($load);
                $links[$load] = $this->buildLink($root, $pk_field, $ids);

            }

            if( count($links) )
                $item->setAttribute('links', $links);
            return $item;

        });

        if( !$is_collection )
            return $instance->pop();

        return $instance;

    }

    public function buildLink($root, $pk_field, $ids) {

        return $this->api_prefix . '/' . $root . '?' . $pk_field . '=' . implode(',', $ids);

    }

}