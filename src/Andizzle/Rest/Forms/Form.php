<?php

abstract class Form implements FormInterface {

    public function validate($request) {}

    public function indexRules() {}

    public function showRules() {}

    public function postRules() {}

    public function putRules() {}

    public function deleteRules() {}

}