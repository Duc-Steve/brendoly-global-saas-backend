<?php

return [

    'paths' => ['api/*'],
    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://brendoly-saas.local:5173',
        'http://localhost:5173',
        'http://127.0.0.1:5173'
    ],
    
    'supports_credentials' => true,
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],
    
    'max_age' => 0,
    
];