<?php namespace Andizzle\Rest\Models;

use ReflectionClass;
use ArrayAccess;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Andizzle\Rest\Database\Builder;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Relations\BelongsToManySelf;
use Andizzle\Rest\Exceptions\ModelNotFoundException as Model404Exception;


abstract class RESTModel extends Model {

    protected $sideLoads = array();

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
     * @return array
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
     * @return array
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

            throw with(new Model404Exception)->setModel(get_called_class())->setCode();

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
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query) {

        return new Builder($query);

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
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray() {

        $attributes = parent::attributesToArray();
        foreach($attributes as $key => $value) {

            if(is_numeric($value))
                $attributes[$key] = intval($value) == floatval($value) ? (int) $value : (float) $value;

        }

        return $attributes;

    }

    /**
     * Get an attribute from the model.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key) {

        $value = parent::getAttribute($key);
        if(is_numeric($value))
            return intval($value) == floatval($value) ? (int) $value : (float) $value;
        return $value;

    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @return array
     */
    protected function getArrayableAttributes() {

        $arrayable_attributes = parent::getArrayableAttributes();

        foreach($this->getArrayableItems(array_flip($this->getMutatedAttributes())) as $key => $value) {
            $arrayable_attributes[$key] = $this->{$key};
        }

        return $arrayable_attributes;

    }

    /**
     * Put the pivot attributes of a relation to the actual object.
     *
     * @return $this
     */
    public function flatPivot() {

        if( $this->pivot ) {

            $pivotPrefix =  str_singular(trim(preg_replace('^(' . $this->getTable() . '|' . str_singular($this->getTable()) . ')^', '', $this->pivot->getTable()), '_'));

            $pivotKeys = array($this->pivot->getKeyName(), $this->pivot->getForeignKey(), $this->pivot->getOtherKey());

            foreach($this->pivot->getAttributes() as $key => $value) {

                if(in_array($key, $pivotKeys))
                    continue;

                if($this->getOriginal($key))
                    $this->setAttribute($pivotPrefix . '_' .$key, $this->pivot->getAttribute($key));
                else
                    $this->setAttribute($key, $value);

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
    public static function lookUp($args, $columns = ['*'], $with = [], $page = 1, $limit = NULL) {

        $instance = new static;

        if($args instanceof Request) {
            $columns = explode(',', $args->input('include', '*'));
            $with    = $args->input('embed') ? explode(',', $args->input('embed')) : [];
            $page    = (int) $args->input('page', $page);
            $limit   = (int) $args->input('limit', $limit);
            $args    = $args->except(['page', 'limit', 'include']);
        }

        $query = $instance->buildLookUpQuery($args, $with, $page, $limit);
        $query->setRetrieveCount(true);

        $result = $query->get($columns);

        $result->pagination = array_merge($result->pagination, [
            'page'  => $page,
            'limit' => $limit
        ]);

        return $result;

    }

    /**
     * Build a lookup query which loops through all lookup functions
     *
     * @param $methods
     * @return Illuminate\Database\Eloquent\Builder $query
     */
    public function buildLookupQuery($methods = [], $with = [], $page = NULL, $limit = NULL) {

        $lookup_query = $this->newQuery()
                             ->select(DB::raw('SQL_CALC_FOUND_ROWS ' . $this->getTable() . '.*'))
                             ->groupBy($this->getTable() . '.' . $this->getKeyName());

        if($page && $limit) {
            $lookup_query->skip(($page - 1) * $limit)
                         ->limit($limit);
        }

        foreach($with as $relation) {

            $method = 'with' . studly_case($relation);
            if( method_exists($this, $method) )
                $lookup_query = $this->{$method}($lookup_query);
            elseif(method_exists($this, $relation))
                $lookup_query = $lookup_query->with($relation);
            else
                continue;

        }

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

        return $query->whereIn($this->getTable() . '.id', $ids);

    }

}