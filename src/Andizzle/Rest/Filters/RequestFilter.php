<?php

namespace Andizzle\Rest\Filters;

use Validator;
use Response;

class RequestFilter {

    /**
     * Check if request inputs are matching a validation rule
     */
    public function filter($route, $request, $rule_loader, $allow_none = false) {

        $rule_loader = trim($rule_loader, '.') . '.';
        list($rule_class, $rule) = explode('.', $rule_loader);
        if( !$rule )
            $rule = strtolower($request->getMethod());
        $input = $request->input();

        if( empty($input) && $allow_none )
            return;

        $ruler = new $rule_class;
        $rules = $ruler->{$rule}();

        $validator = Validator::make($input, $rules);

        if( $validator->fails() )
            return Response::json(array('messages' => $validator->messages()->all()), 400);

        foreach( $input as $key => $value ) {

            if( !array_key_exists($key, $rules) )
                array_forget($input, $key);

        }

        $request->replace($input);

    }

    private function makeRules($rule) {

    }

    private function processRequest() {

    }

}