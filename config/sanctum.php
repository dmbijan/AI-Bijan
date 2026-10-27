<?php

use Laravel\Sanctum\Sanctum;

return [
    'stateful' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
            '%s%s',
            'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,',
            Sanctum::currentApplicationUrlWithPort()
        )))
    ))),
];
