<?php

namespace Andizzle\Rest\Models;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Andizzle\Rest\Relations\BelongsToManySelf;


class RESTModel extends Model {

    public $root = '';
    protected $sideLoads = array();

    public function __construct(array $attributes = array()) {

        parent::__construct($attributes);
        if( !$this->root )
            $this->root = $this->getRoot();

    }

    /**
     * Get the root of model for the payload
     *
     * @return string
     */
    public function getRoot() {

        if( $this->root )
            return $this->root;

        return str_plural(strtolower(class_basename(get_class($this))));

    }

    /**
     * Set sideLoads attributes
     *
     * @return RESTModel
     */
    public function setWith(array $relations) {

        $this->with = $relations;
        return $this;

    }

    /**
     * Get sideLoads attributes
     *
     * @return RESTModel
     */
    public function getWith() {

        return $this->with;

    }

    /**
     * Set sideLoads attributes
     *
     * @return RESTModel
     */
    public function setSideLoads(array $relations) {

        $this->sideLoads = $relations;
        return $this;

    }

    /**
     * Get sideLoads attributes
     *
     * @return RESTModel
     */
    public function getSideLoads() {

        return $this->sideLoads;

    }

    /**
     * Load an object from id and cast it to models's subclass
     *
     * @return RESTModel
     */
    public static function loads($id, $postLoad = false) {

        try {

            $instance = self::findOrFail($id);

        } catch(ModelNotFoundException $e) {

            throw new Exception(sprintf("No %s Found.", class_basename(get_called_class())));

        }

        $caller = get_called_class();

        return $caller::cast($instance);

    }

    /**
     * Cast an instance to something. This is meant to be overwritten
     * in subclasses.
     *
     * @return RESTModel
     */
    public static function cast($instance) {

        return $instance;

    }

    /**
     * Return the model as an array with pivot flatted into model.
     *
     * @return array
     */
    public function toArray() {

        $this->flatPivot();
        return parent::toArray();

    }

    /**
     * Put the pivot attributes of a relation to the actual object.
     *
     * @return $this
     */
    public function flatPivot() {

        if( $this->pivot ) {

            $pivotPrefix =  str_singular(preg_replace('^(.*)_^', '', $this->pivot->getTable()));

            $pivotKeys = array($this->pivot->getKeyName(), $this->pivot->getForeignKey(), $this->pivot->getOtherKey());

            foreach($this->pivot->getAttributes() as $key => $value) {

                if( !in_array($key, $pivotKeys ) )
                    $this->setAttribute($pivotPrefix . '_' .$key, $value);

            }

        }

        return $this;

    }

    /**
     * Define a self many-to-many relationship.
     * This function is a copy of belongsToMany except it returns a
     * BelongsToManySelf object
     *
     * @param  string  $related
     * @param  string  $table
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $relation
     * @return Relations\BelongsToMany
     */
    public function belongsToManySelf($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null) {

        if (is_null($relation))
            $caller = $this->getBelongsToManyCaller();

        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = new $related;

        $otherKey = $otherKey ?: $instance->getForeignKey();

        if (is_null($table))
            $table = $this->joiningTable($related);

        $query = $instance->newQuery();

        return new BelongsToManySelf($query, $this, $table, $foreignKey, $otherKey, $relation);
    }

    /**
     * Perform a lookup base on inputs
     *
     * @param array $args
     * @param optional $columns
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function lookUp(array $args, $columns = array('*')) {

        $instance = new static;
        $lookup_query = $instance->buildLookUpQuery($args);
        return $lookup_query->get($columns);

    }

    /**
     * Build a lookup query which loops through all lookup functions
     *
     * @param $methods
     * @return Illuminate\Database\Eloquent\Builder $query
     */
    public function buildLookupQuery($methods = array()) {

        $lookup_query = $this->newQuery();

        foreach($methods as $by => $value) {

            $method = 'lookUpBy' . studly_case($by);
            if( !method_exists($this, $method) )
                continue;

            $lookup_query = $this->{$method}($lookup_query, $value);

        }

        return $lookup_query;

    }

    /**
     * Build a query to select by ids
     *
     * @param Illuminate\Database\Eloquent\Builder $query
     * @param $ids
     * @return Illuminate\Database\Eloquent\Builder $query
     */
    public function lookUpByIds(Builder $query, $ids) {

        if( is_string($ids) )
            $ids = explode(',', $ids);

        return $query->whereIn('id', $ids);

    }

}