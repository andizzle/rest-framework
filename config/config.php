<?php

return [
    'version' => 'v1',
    'case' => 'camelCase',
    'limit' => 20,
    'limit_max' => 2000,
    'sideload_limit' => 1,
    'serializer' => [
        'model' => 'Andizzle\Rest\Serializers\BaseSerializer',
        'embed-relations' => false
    ],
    'authorization' => [
        'provider' => NULL
    ],
    'authentication' => [
        'provider' => NULL
    ],
    'session_prefix' => 'rest'
];