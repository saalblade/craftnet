<?php

return [
    '*' => [
        'useDevServer' => false,
        'manifest' => [
            'legacy' => 'manifest.json',
            'modern' => 'manifest.json',
        ],
        'server' => [
            'manifestPath' => './craftnetresources/id/dist/',
            'publicPath' => '/craftnetresources/id/dist/',
        ],
        'devServer' => [
            'manifestPath' => 'https://localhost:8081/',
            'publicPath' => 'https://localhost:8081/',
        ],
    ],
    'dev' => [
        'useDevServer' => true,
    ],
];
