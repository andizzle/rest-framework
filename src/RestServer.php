<?php

namespace Andizzle\Rest;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;


class RestServer {

    protected $meta = [];

    /**
     * Figure out the api prefix base on incoming request.
     *
     * @return string
     */
    public function getApiPrefix() {

        $prefix = '';

        $api_versions = Config::get('rest.deprecated') ?: array();
        $api_versions[] = Config::get('rest.version');

        $segments = Request::segments();
        foreach( $segments as $segment ) {

            $prefix .= '/' . $segment;
            if( in_array($segment, $api_versions) )
                break;

        }

        return $prefix;

    }

    public function getMeta($key) {

        return array_get($this->meta, $key);

    }

    public function setMeta($key = '', $value = NULL) {

        array_set($this->meta, $key, $value);
        return $this->meta;

    }

    public function setRequestMeta() {

        $this->meta['page'] = (int) Request::input('page') ?: 1;

        if((int) Request::input('per_page') && (int) Request::input('per_page') < Config::get('rest.per_page_max'))
            $this->meta['per_page'] = (int) Request::input('per_page');
        else
            $this->meta['per_page'] = Config::get('rest.per_page');

    }

    /**
     * Return an error response with code and content.
     *
     * @param int $code
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function errorResponse($code, $message = '') {

        $error = [
            'errors' => [
                $message
            ]
        ];
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

        $case = $case ?: Config::get('rest.case');
        return $this->convertInput($input, $case);

    }

    private function convertInput(array &$input, $case) {

        foreach($input as $key => $value) {

            if(is_array($value))
                $this->convertInput($value, $case);

            $converted_key = $key;
            switch($case) {
            case 'snakeCase':
                $converted_key = snake_case($key);
                break;
            case 'camelCase':
                $converted_key = camel_case($key);
                break;
            case 'studlyCase':
                $converted_key = studly_case($key);
                break;
            default:
                break;
            }

            if($key !== $converted_key) {
                $input[$converted_key] = $value;
                unset($input[$key]);
            }

        }

        return $input;

    }

}