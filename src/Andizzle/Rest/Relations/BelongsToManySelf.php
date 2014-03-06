<?php

namespace Andizzle\Rest\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class BelongsToManySelf extends BelongsToMany {

    /**
     * Because it's a self belongs to many, we add the other key to
     * dictionary as well.
     *
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @return array
     */
    protected function buildDictionary(Collection $results) {

        $foreign = $this->foreignKey;
        $other = $this->otherKey;

        // First we will build a dictionary of child models keyed by the
        // foreign key of the relation so that we will easily and quickly match them to
        // their parents without having a possibly slow inner loops for every
        // models.
        $dictionary = array();

        foreach ($results as $result) {
            $dictionary[$result->pivot->$foreign][] = $result;
            $dictionary[$result->pivot->$other][] = $result;
        }

        return $dictionary;

    }

    /**
     * Set the where clause for the relation query.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected function setWhere() {
        return $this;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     * @return void
     */
    public function addEagerConstraints(array $models) {
        //$this->query->whereIn($this->getForeignKey(), $this->getKeys($models));
    }

    /**
     * Set the join clause for the relation query.
     *
     * @param \Illuminate\Database\Eloquent\Builder|null
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    protected function setJoin($query = null) {

        $query = $query ?: $this->query;

        // We need to join to the intermediate table on the related model's
        // primary
        // key column with the intermediate table's foreign key for the
        // related
        // model instance. Then we can set the "where" for the parent models.
        $baseTable = $this->related->getTable();

        $key = $baseTable.'.'.$this->related->getKeyName();

        //$query->join($this->table, $key, '=', $this->getOtherKey());
        $query->join($this->table, function($join) use ($key)
        {
            $join->on($key, '=', $this->getOtherKey())->where($this->getForeignKey(), '=', $this->parent->getKey())
                 ->orOn($key, '=', $this->getForeignKey())->where($this->getOtherKey(), '=', $this->parent->getKey());
        });

        return $this;

    }

}