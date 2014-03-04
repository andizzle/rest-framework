<?php

use Mockery as m;
use Andizzle\Rest\Models\RESTModel;
use Illuminate\Database\Eloquent\Collection;
use Andizzle\Rest\Serializers\BaseSerializer;


class BaseSerializerTest extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        m::close();
    }

    public function testSerializeSingle() {

        $obj = new RESTModelStub;
        $obj->foo = 'bar';
        $serializer = new BaseSerializer;
        $this->assertEquals(array('fred' => array('foo' => 'bar')), $serializer->serialize($obj, 'fred'));

    }

    public function testSerializeCollection() {

        $obj = new RESTModelStub;
        $obj->foo = 'bar';
        $collection = new Collection;
        $collection->push($obj);
        $serializer = new BaseSerializer;
        $this->assertEquals(array('freds' => array(array('foo' => 'bar'))), $serializer->serialize($collection, 'fred'));

    }

    public function testSerializeNested() {

        $obj = new RESTModelStub;
        $fooobj = new RESTModelStub;
        $fooobj->foo = 'bar';
        $collection = new Collection;
        $collection->push($fooobj);
        $obj->setRelation('foos', $collection);
        $serializer = new BaseSerializer;
        $this->assertEquals(array('fred' => array('foos' => array(array('foo' => 'bar')))), $serializer->serialize($obj, 'fred'));

    }

    public function testGetRoot() {

        $serializer = new BaseSerializer;
        $obj = new RESTModelStub;
        $collection = new Collection;
        $this->assertEquals('foo', $serializer->getRoot($obj, 'foo'));
        $this->assertEquals('foos', $serializer->getRoot($collection, 'foo'));

    }

}

class RESTModelStub extends RESTModel {

    public function load($relations) {
        return $this;
    }

}