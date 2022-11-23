<?php

return [

    'url' => env('PLOI_URL'),
    'key' => env('PLOI_KEY'),

    'from_server' => env('PLOI_FROM_SERVER'),
    'to_server' => env('PLOI_TO_SERVER'),

    'sites' => [
        'staging-api.112pers.nl' => [
            'repo' => [
                'provider' => 'bitbucket',
                'branch' => 'staging',
                'name' => '112pers/api.112pers.nl'
            ]
        ]
    ]
];
