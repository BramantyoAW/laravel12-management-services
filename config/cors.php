<?php 

return [

    'paths' => ['api/*', 'graphql'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'], // Ganti dengan alamat frontend kamu

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
