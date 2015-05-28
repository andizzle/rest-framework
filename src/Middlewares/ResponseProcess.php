<?php namespace Andizzle\Rest\Middlewares;

use Config;
use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Routing\Middleware;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Facades\SerializerFacade as Serializer;

class ResponseProcess implements Middleware{

    public function handle($request, Closure $next) {

        $response = $next($request);
        $response = $this->createResponse($request, $response);
        return $response;

    }

    /**
     * Create metadata for the response
     *
     * @return mix
     */
    public function createMetadata($result, $request) {

        if( !$result instanceof Collection )
            return [];

        $metadata = [
            'meta' => [
                'total' => REST::getMeta('total')
            ]
        ];

        array_set($metadata, 'meta.page', (int) $request->input('page'));
        array_set($metadata, 'meta.limit', (int) $request->input('per_page'));

        return $metadata;

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
    public function createResponse($request, &$response) {

        if($response instanceof Response) {

            $original_content = $response->getOriginalContent();

            if(!$original_content || is_array($original_content))
                return $response;

            if($request->session()->get('rest.serializer')) {
                Serializer::swap(App::make($request->session()->get('rest.serializer')));
            }

            $metadata = $this->createMetadata($original_content, $request);

            $result = Serializer::serialize($original_content, $request->session()->get('rest.doc_root'));
            $result = array_merge($metadata, $result);

            $response->setContent(Serializer::dehydrate($result));

        }

        return $response;

    }

}