<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class HyperlinkedJSONSerializer extends BaseSerializer {

    protected $api_prefix = '';
    protected $url_overrides = array();

    public function __construct() {

        $this->page_limit = Config::get('andizzle/rest-framework::page_limit');
        $this->api_prefix = REST::getApiPrefix();

    }

    /**
     * Set the url override for the serializer instnace.
     *
     * @param array $urls
     * @return Andizzle\Rest\Serializers\HyperlinkedJSONSerializer
     */
    public function setURLOverrides(array $urls) {

        $this->url_overrides = $urls;
        return $this;

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
            return $this->serializeRelations($item);
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

        $links = array();
        $side_loads = $instance->getSideLoads();

        foreach($side_loads as $load) {

            $relation = $instance->{$load};
            $instance->__unset($load);
            if($this->isEmptyOrNull($relation))
                continue;

            if( array_key_exists($load, $this->url_overrides) )
                $links[$load] = $this->url_overrides[$load];
            else
                $links[$load] = $this->buildLink($relation);

        }

        if( count($links) )
            $instance->setAttribute('links', $links);

        return $instance;

    }

    /**
     * Build the link to resource.
     *
     * @param string $root
     * @param string $pk_field
     * @param array $ids
     * @return string
     */
    public function buildLink($instance) {

        $link = '';
        $is_collection = true;

        if($instance instanceof Collection) {

            $instance = $instance->unique()->take($this->page_limit);

        } else {

            $collection = new Collection;
            $instance = $collection->add($instance);
            $is_collection = false;

        }

        $root = $instance->first()->getRoot();
        $pk_field = str_plural($instance->first()->getKeyName());
        $ids = $instance->modelKeys();

        if( $is_collection )
            return $this->api_prefix . '/' . $root . '?' . $pk_field . '=' . implode(',', $ids);

        return $this->api_prefix . '/' . $root . '/' . implode(',', $ids);

    }

}