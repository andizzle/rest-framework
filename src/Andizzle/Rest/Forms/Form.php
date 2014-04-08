<?php

namespace Andizzle\Rest\Forms;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Andizzle\Rest\Exceptions\InputValidationException;


abstract class Form implements FormInterface {

    public $message = array();

    /**
     * Create a validator from request and rules.
     *
     * @param $request
     * @param $rules
     * @return Illuminate\Validation\Validator
     */
    public function validate($request, array $rules = array()) {

        $rules = !empty($this->rules) ? $this->rules : $rules;

        return Validator::make(
            $request->all(),
            $rules,
            $this->message
        );

    }

    /**
     * Get controller action from $route and return the rule for it.
     *
     * @param $route
     * @return array
     */
    public function getRules($route) {

        $action = $route->getAction();
        $method = substr($action['controller'], strpos($action['controller'], '@') + 1) . 'Rule';

        if(method_exists($this, $method)) {

            return $this->{$method}();

        }

        return array();

    }

}