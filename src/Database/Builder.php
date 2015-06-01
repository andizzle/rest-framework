<?php namespace Andizzle\Rest\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;
use Andizzle\Rest\Facades\RestServerFacade as REST;


class Builder extends BaseBuilder {

    protected $retrieve_count = false;

    public function setRetrieveCount($value) {

        $this->retrieve_count = $value;

    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function get($columns = array('*')) {
        $models = $this->getModels($columns);

        if($this->retrieve_count) {
            $total = DB::select(DB::raw('SELECT FOUND_ROWS() AS total;'))[0]->total;
        }

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded, which will solve the
        // n+1 query issue for the developers to avoid running a lot of queries.
        if (count($models) > 0)
            $models = $this->eagerLoadRelations($models);

        $result = $this->model->newCollection($models);
        $result->pagination = ['total' => $total];
        return $result;
    }

}