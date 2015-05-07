<?php

namespace Andizzle\Rest\Exceptions;


class BadRequestException extends \RuntimeException implements RESTExceptionInterface {

    public function setMessage($message = 'Bad Request!') {

        $this->message = $message;
        return $this;

    }

    public function setCode($code = 400) {

        $this->code = $code;
        return $this;

    }

}