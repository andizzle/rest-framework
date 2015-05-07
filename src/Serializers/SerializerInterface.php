<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Contracts\Support\Arrayable;


interface SerializerInterface {

    public function serialize(Arrayable $instance, $root);

    public function dehydrate(array $data);

}