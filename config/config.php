<?php

return [
    'version' => 'v1',
    'case' => 'camelCase',
    'per_page' => 20,
    'per_page_max' => 2000,
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