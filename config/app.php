<?php

return [
    'modules' => [
        'api' => \craftcom\api\Module::class
    ],
    'components' => [
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => 'craft.4qveoj.ng.0001.usw2.cache.amazonaws.com',
            'port' => 6379,
            'database' => 0,
        ],
        'cache' => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'craft.4qveoj.ng.0001.usw2.cache.amazonaws.com',
                'port' => 6379,
                'database' => 0,
            ],
        ],
        'session' => [
            'class' => 'yii\redis\Session',
        ],
    ]
];
