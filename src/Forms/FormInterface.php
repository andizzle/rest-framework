<?php

namespace Andizzle\Rest\Forms;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;


interface FormInterface {

    public function validate($request, array $rules = array());

    public function getRules($route);

}