<?php

namespace Andizzle\Rest\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Facades\SerializerFacade as Serializer;


abstract class RESTController extends Controller {

    protected $root = '';
    protected $serializer = null;
    protected $serialize_with_relation = true;
    protected $validation_form = '';

    protected $auth_filters = array();
    protected $request_filters = array(
        '@preprocessRequest',
        '@validateRequest'
    );
    protected $response_filters = array(
        '@createResponse'
    );

    /**
     * Create our rest controller, define the serializer, add in
     * filters etc
     *
     * @return void
     */
    public function __construct() {

        // if a different serializer is used, the serializer can be an
        // alias :)
        if( $this->serializer )
            Serializer::swap(App::make($this->serializer));

        // here we add before filters to do auth and preprocess the
        // request
        $before_filters = array_merge($this->auth_filters, $this->request_filters);
        foreach($before_filters as $before_filter) {
            $this->beforeFilter($before_filter);
        }

        // here we add after filters to process the response
        foreach($this->response_filters as $response_filter) {
            $this->afterFilter($response_filter);
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
     * Serialize the response with a serializer. This function is for
     * after filter.
     *
     * @param $route
     * @param $request
     * @param $response
     * @return void
     */
    public function createResponse($route, $request, $response) {

        $original_content = $response->getOriginalContent();

        if(!$original_content || is_array($original_content))
            return;

        $result = Serializer::serialize($original_content, $this->root, $this->serialize_with_relation);
        $response->setContent(Serializer::dehydrate($result));

    }

    /**
     * Process the request and replace the input
     *
     * @param $route
     * @param $request
     * @return void
     */
    public function preprocessRequest($route, $request) {

        $input = REST::convertCase($request->all(), 'snakeCase');
        $input = $this->handleRequest($input);
        $request->replace($input);

    }

    /**
     * Validate request and filter out extra parameters
     *
     * @param $route
     * @param $request
     * @return void
     */
    public function validateRequest($route, $request) {

        if( !$this->validation_form )
            return;

        $form = App::make($this->validation_form);
        $validate = $form->getAction($route)->validate($request);

        // if the validation fails, return an error response
        if($validate->fails()) {

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

        $error = array(
            'status' => 'failed',
            'message' => $message
        );
        return Response::json($error, $code);

    }

}