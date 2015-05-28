<?php namespace Andizzle\Rest\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Exceptions\InputValidationException;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Facades\SerializerFacade as Serializer;


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
            'rest.doc_root'   => $this->root,
            'rest.serializer' => $this->serializer
        ]);

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