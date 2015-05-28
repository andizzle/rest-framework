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

        // if a different serializer is used, the serializer can be an
        // alias :)
        if( $this->serializer ) {
            Serializer::swap(App::make($this->serializer));
        }

    }

    /**
     * Get model root
     *
     * @return string
     */
    public function getRoot() {

        return $this->root;

    }

    /**
     * Set model root.
     *
     * @return RESTModel
     */
    public function setRoot($root) {

        $this->root = $root;
        return $this;

    }

    /**
     * Validate request and filter out extra parameters
     *
     * @param $route
     * @param $request
     * @return void
     * @throws InputValidationException
     */
    public function validateRequest($route, $request) {

        if( !$this->validation_form )
            return;

        $form = App::make($this->validation_form);

        // if the validation fails, return an error response
        try {

            $form->validate($request, $form->getRules($route));

        } catch(InputValidationException $e) {

            return Response::json(['status' => 'failed', 'errors' => explode('|', $e->getMessage())], $e->getCode());

        }

    }

    /**
     * Manipulate the request input and return changed result.
     *
     * @param array $input
     * @return array
     */
    public function handleRequest(array $input) {

        return $input;

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