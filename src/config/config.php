<?php

return array(

    'version' => 'v1',
    'case' => 'camelCase',
    'page_limit' => 20,
    'page_limit_max' => 200,
    'sideload_limit' => 1,
    'serializer' => array(
        'model' => 'Andizzle\Rest\Serializers\Serializer',
        'embed-relations' => false
    ),
    'authorization' => array(
        'provider' => ''
    ),
    'authentication' => array(
        'provider' => ''
    )

);