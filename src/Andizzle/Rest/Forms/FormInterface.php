<?php

interface FormInterface {

    public function validate();

    public function makeRule();

    public function indexRules();

    public function showRules();

    public function postRules();

    public function putRules();

    public function deleteRules();

}