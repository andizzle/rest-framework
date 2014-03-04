<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;


interface SerializerInterface {

    public function serialize(ArrayableInterface $instance, $root, $withRelations);

    public function dehydrate(array $data);

}