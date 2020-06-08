<?php

return [

/*
|--------------------------------------------------------------------------
| Laravel CORS
|--------------------------------------------------------------------------
|
| allowedOrigins, allowedHeaders and allowedMethods can be set to array('*')
| to accept any value.
|
*/
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:8000', 'https://royal7.netlify.app'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => false,
    'max_age' => false,
    'supports_credentials' => false,
    'paths' => ['api/*'],
];