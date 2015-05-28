<?php namespace Andizzle\Rest\Middlewares;

use Config;
use Closure;
use REST;
use Illuminate\Contracts\Routing\Middleware;

class RequestProcess implements Middleware{

    protected $case       = 'snakeCase';
    protected $pagination = [
        'page'     => 1,
        'per_page' => 1
    ];
    protected $per_page_max = 0;

    public function __construct() {

        $this->per_page_max           = Config::get('rest.per_page_max');
        $this->pagination['per_page'] = Config::get('rest.per_page');

    }

    public function handle($request, Closure $next) {

        $this->setupPagination($request);
        $this->convertCase($request);
        return $next($request);

    }

    public function setupPagination(&$request) {

        if($per_page = $request->input('per_page')) {
            if($per_page < $this->per_page_max) {
                $per_page = $this->per_page_max;
            }
            $this->pagination['per_page'] = $per_page;
        }

        if($page = $request->input('page')) {
            $this->pagination['page'] = $page;
        }

        $request->merge($this->pagination);

    }

    public function convertCase(&$request) {

        $input = REST::convertCase($request->all(), $this->case);
        $request->replace($input);

    }

}