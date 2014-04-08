<?php

namespace Andizzle\Rest\Exceptions;


class InputValidationException extends \RuntimeException {

    public function setMessage($messages) {

        $this->message = implode('|', $messages);
        return $this;

    }

    public function setCode($code = 400) {
        $this->code = $code;
        return $this;
    }

}