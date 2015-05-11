<?php

use Mockery as m;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Serializers\JSONSerializer;


class JSONSerializerTest extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        m::close();
    }

    public function testSerializeSingle() {

        Config::shouldReceive('get')->with('rest.page_limit')->andReturn('5');
        Config::shouldReceive('get')->with('rest.serializer.embed-relations')->once()->andReturn(true);

        $obj = new RESTModelStub;
        $obj->foo = 'bar';

        $fooobj = new RESTModelStub;
        $fooobj->id = 1;
        $fooobj->root = 'roots';

        $collection = new Collection;

        $obj->setSideLoads(array('foos'));

        $collection->push($fooobj);
        $obj->setRelation('foos', $collection);
        $serializer = new JSONSerializer;
        $this->assertEquals(array('fred' => array('foo' => 'bar', 'foos' => array('1')), 'foos' => array(array('id' => 1))), $serializer->serialize($obj, 'fred'));

    }

    public function testSerializeNoEmbed() {

        Config::shouldReceive('get')->with('rest.page_limit')->andReturn('5');
        Config::shouldReceive('get')->with('rest.serializer.embed-relations')->once()->andReturn(false);

        $obj = new RESTModelStub;
        $obj->foo = 'bar';

        $fooobj = new RESTModelStub;
        $fooobj->id = 1;
        $fooobj->root = 'roots';

        $collection = new Collection;

        $obj->setSideLoads(array('foos'));

        $collection->push($fooobj);
        $obj->setRelation('foos', $collection);
        $serializer = new JSONSerializer;

        $this->assertEquals(array('fred' => array('foo' => 'bar', 'foos' => array('1'))), $serializer->serialize($obj, 'fred'));

    }

}
