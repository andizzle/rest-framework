<?php

return [
    'version' => 'v1',
    'case' => 'camelCase',
    'page_limit' => 20,
    'page_limit_max' => 200,
    'sideload_limit' => 1,
    'serializer' => [
        'model' => 'Andizzle\Rest\Serializers\Serializer',
        'embed-relations' => false
    ],
    'authorization' => [
        'provider' => ''
    ],
    'authentication' => [
        'provider' => ''
    ]
];