<?php

namespace Andizzle\Rest\Exceptions;


class ModelNotFoundException extends \RuntimeException implements RESTExceptionInterface {

    /**
     * Name of the affected Eloquent model.
     *
     * @var string
     */
    protected $model;

    /**
     * Set the affected Eloquent model.
     *
     * @param string $model
     * @return ModelNotFoundException
     */
    public function setModel($model) {

        $this->model = $model;
        $this->message = sprintf("No %s Found.", class_basename($model));

        return $this;

    }

    /**
     * Get the affected Eloquent model.
     *
     * @return string
     */
    public function getModel() {

        return $this->model;

    }

    public function setCode($code = 404) {

        $this->code = $code;
        return $this;

    }

}