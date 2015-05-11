<?php

use Mockery as m;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Andizzle\Rest\RestServer;


class RestServerTest extends PHPUnit_Framework_TestCase {

    public function tearDown() {
        m::close();
    }

    public function testGetApiPrefix() {

        Config::shouldReceive('get')->with('rest.deprecated')->andReturn(array());
        Config::shouldReceive('get')->with('rest.version')->andReturn('v1');
        Request::shouldReceive('segments')->once()->andReturn(array('api', 'v1', 'test'));
        $server = new RestServer();
        $this->assertEquals($server->getApiPrefix(), '/api/v1');

    }

    public function testConvertCase() {

        $snake_input = array('test_case' => 1);
        $camel_input = array('testCase' => 1);
        $studly_input = array('TestCase' => 1);

        $server = new RestServer();
        $this->assertEquals($server->convertCase($snake_input, 'camelCase'), $camel_input);
        $this->assertEquals($server->convertCase($camel_input, 'snakeCase'), $snake_input);
        $this->assertEquals($server->convertCase($snake_input, 'studlyCase'), $studly_input);

    }

}