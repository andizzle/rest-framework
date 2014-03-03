<?php

namespace Andizzle\Rest\Serializers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Contracts\ArrayableInterface;


interface SerializerInterface {

    public function serialize(ArrayableInterface $instance, $root, $withRelations);

    public function serializeRelations(ArrayableInterface $instance);

    public function collectRelations(Collection $instance);

    public function mergeRelations(array $result);

}