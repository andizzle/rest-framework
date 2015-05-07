<?php

namespace Andizzle\Rest;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;


class RestServer {

    /**
     * Figure out the api prefix base on incoming request.
     *
     * @return string
     */
    public function getApiPrefix() {

        $prefix = '';

        $api_versions = Config::get('andizzle/rest-framework::deprecated') ?: array();
        array_push($api_versions, Config::get('andizzle/rest-framework::version'));

        $segments = Request::segments();
        foreach( $segments as $segment ) {

            $prefix .= '/' . $segment;
            if( in_array($segment, $api_versions) )
                break;

        }

        return $prefix;

    }

    /**
     * Return an error response with code and content.
     *
     * @param int $code
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($code, $message = '') {

        $error = array(
            'errors' => array(
                $message
            )
        );
        return Response::json($error, $code);

    }

    /**
     * Convert a key pair array to a certain format.
     *
     * @param array $input
     * @param string $case
     * @return array
     */
    public function convertCase(array $input, $case = null) {

        $case = Config::get('andizzle/rest-framework::case');
        return $this->{$case . 'Input'}($input);

    }

    /**
     * Snakecase an array's keys.
     *
     * @param array $input
     * @return array
     */
    private function snakeCaseInput(array $input) {

        $convertedInput = array();
        foreach($input as $key => $value) {

            if(is_array($value)) {
                // Recursively run to loop through all keys
                $value = $this->snakeCaseInput($value);
            }

            $convertedInput[snake_case($key)] = $value;

        }

        return $convertedInput;

    }

    /**
     * Camelcase an array's keys.
     *
     * @param array $input
     * @return array
     */
    private function camelCaseInput(array $input) {

        $convertedInput = array();
        foreach($input as $key => $value) {

            if(is_array($value)) {
                // Recursively run to loop through all keys
                $value = $this->camelCaseInput($value);
            }

            $convertedInput[camel_case($key)] = $value;

        }

        return $convertedInput;

    }


    /**
     * Studlycase an array's keys.
     *
     * @param array $input
     * @return array
     */
    private function studlyCaseInput(array $input) {

        $convertedInput = array();
        foreach($input as $key => $value) {

            if(is_array($value)) {
                // Recursively run to loop through all keys
                $value = $this->studlyCaseInput($value);
            }

            $convertedInput[studly_case($key)] = $value;

        }

        return $convertedInput;

    }

}