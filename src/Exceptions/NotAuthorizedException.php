<?php

namespace Andizzle\Rest\Exceptions;


class NotAuthorizedException extends \RuntimeException implements RESTExceptionInterface {

    public function setMessage($message = 'Action Unauthorized!') {

        $this->message = $message;
        return $this;

    }

    public function setCode($code = 403) {

        $this->code = $code;
        return $this;

    }

}