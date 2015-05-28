<?php namespace Andizzle\Rest\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Request;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class RESTController extends Controller {

    use DispatchesCommands, ValidatesRequests;

    protected $root = NULL;
    protected $serializer = NULL;

    /**
     * Create our rest controller, define the serializer, add in
     * filters etc
     *
     * @return void
     */
    public function __construct() {

        Request::merge([
            'rest.doc_root'   => $this->getRoot(),
            'rest.serializer' => $this->serializer
        ]);

    }

    public function getRoot() {

        return $this->root ?: $this->guessRoot();

    }

    public function guessRoot() {

        $class = str_replace('Controller', '', class_basename($this));
        return strtolower(str_singular($class));

    }

    /**
     * Return an error response with code and content.
     *
     * @param int $code
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public static function errorResponse($code, $message = '') {

        $error = [
            'status' => 'failed',
            'message' => $message
        ];
        return Response::json($error, $code);

    }

}