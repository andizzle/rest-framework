<?php

namespace Andizzle\Rest\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Exceptions\InputValidationException;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Facades\SerializerFacade as Serializer;


abstract class RESTController extends Controller {

    protected $root = '';
    protected $page = 1;
    protected $per_page = 0;
    protected $per_page_max = 0;
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

        $this->per_page = Config::get('andizzle/rest-framework::per_page');
        $this->per_page_max = Config::get('andizzle/rest-framework::per_page_max');

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

        if($response instanceof \Illuminate\Http\Response) {

            $original_content = $response->getOriginalContent();

            if(!$original_content || is_array($original_content))
                return;

            $metadata = $this->createMetadata($original_content, $request);
            $original_content = $this->paginate($original_content, $this->page, $this->per_page);
            $result = Serializer::serialize($original_content, $this->root);
            $result = array_merge($metadata, $result);
            $response->setContent(Serializer::dehydrate($result));
        }
    }

    /**
     * Create metadata for the response
     *
     * @return mix
     */
    public function createMetadata($result, $request) {

        if( !$result instanceof Collection )
            return array();

        $metadata = array(
            'meta' => array(
                'total' => $result->count()
            )
        );

        if( $page = $request->input('page') )
            $this->page = $page;

        if( $per_page = $request->input('per_page') )
            $this->per_page = $per_page;

        $this->per_page = $this->per_page > $this->per_page_max ? $this->per_page_max : $this->per_page;

        array_set($metadata, 'meta.page', intval($this->page));
        array_set($metadata, 'meta.limit', intval($this->per_page));

        return $metadata;

    }

    /**
     * Paginate the result.
     *
     * @param $result
     * @param int $page
     * @param int $limit
     * @return mix
     */
    public function paginate($result, $page = 1, $limit = null) {

        if( !$result instanceof Collection )
            return $result;

        $offset = ($page - 1) * $limit;
        return $result->slice($offset, $limit);

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

            return Response::json(array('status' => 'failed', 'errors' => explode('|', $e->getMessage())), $e->getCode());

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