<?php

use Mockery as m;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator as ValidatorClass;
use Andizzle\Rest\Controllers\RESTController;
use Andizzle\Rest\Forms\Form;
use Illuminate\Support\Facades\Validator as Validator;


class RESTControllerTest extends PHPUnit_Framework_TestCase {

    public function setUp() {

    }

    public function tearDown() {
        m::close();
    }

    public function testFormValidation() {

        // $route = m::mock('Route');
        // $route->shouldReceive('getAction')->andReturn(array('controller' => 'indexRule'));

        // $request = m::mock('Request');
        // $request->shouldReceive('all')->andReturn(array('name' => 'test'));

        // App::shouldReceive('make')->andReturn(new FormStub);
        // Validator::shouldReceive('make')->andReturn(new ValidatorClass($this->getRealTranslator(), $request->all(), array('name' => array('required', 'min:5'))));

        // $controller = new RESTControllerStub;
        // $controller->validateRequest($route, $request);

    }

    protected function getRealTranslator() {
        $trans = new Symfony\Component\Translation\Translator('en', new Symfony\Component\Translation\MessageSelector);
        $trans->addLoader('array', new Symfony\Component\Translation\Loader\ArrayLoader);
        return $trans;
    }

}

class RESTControllerStub extends RESTController {

    public function __construct() {}

    protected $validation_form = 'FormStub';

}

class FormStub extends Form {

    public function indexRule() {
        return array('name' => array('required', 'min:5'));
    }

}