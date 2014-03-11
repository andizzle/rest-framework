<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Support\Contracts\ArrayableInterface;


interface SerializerInterface {

    public function serialize(ArrayableInterface $instance, $root);

    public function dehydrate(array $data);

}