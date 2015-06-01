<?php

namespace Andizzle\Rest;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;


class RestServer {

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