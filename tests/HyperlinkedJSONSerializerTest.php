<?php

use Mockery as m;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Facades\RestServerFacade as REST;
use Andizzle\Rest\Serializers\HyperlinkedJSONSerializer;


class HyperlinkedJSONSerializerTest extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        m::close();
    }

    public function testSerializeSingle() {

        Config::shouldReceive('get')->with('andizzle/rest-framework::page_limit')->andReturn('5');
        REST::shouldReceive('getApiPrefix')->andReturn('api/v1');

        $obj = new RESTModelStub;

        $fooobj = new RESTModelStub;
        $fooobj->id = 1;
        $fooobj->root = 'roots';

        $collection = new Collection;

        $obj->setSideLoads(array('foos'));

        $collection->push($fooobj);
        $obj->setRelation('foos', $collection);
        $serializer = new HyperlinkedJSONSerializer;
        $this->assertEquals(array('fred' => array('links' => array('foos' => 'api/v1/roots?ids=1'))), $serializer->serialize($obj, 'fred'));

    }

}
