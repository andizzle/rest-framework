<?php namespace Andizzle\Rest\Middlewares;

use Config;
use Closure;
use REST;
use Illuminate\Contracts\Routing\Middleware;

class RequestProcess implements Middleware{

    protected $case       = 'snakeCase';
    protected $pagination = [
        'page'     => 1,
        'limit'    => 1
    ];
    protected $limit_max = 0;

    public function __construct() {

        $this->limit_max           = Config::get('rest.limit_max');
        $this->pagination['limit'] = Config::get('rest.limit');

    }

    public function handle($request, Closure $next) {

        $this->setupPagination($request);
        $this->convertCase($request);
        return $next($request);

    }

    public function setupPagination(&$request) {

        if($limit = (int) $request->input('limit')) {
            if($limit > $this->limit_max) {
                $limit = $this->limit_max;
            }
            $this->pagination['limit'] = $limit > 0 ? $limit : 1;
        }

        if($page = (int) $request->input('page')) {
            $this->pagination['page'] = $page;
        }

        $request->merge($this->pagination);

    }

    public function convertCase(&$request) {

        $input = REST::convertCase($request->all(), $this->case);
        $request->replace($input);

    }

}