<?php

return [
    '*' => [
        'modules' => [
            'api' => \craftcom\api\Module::class,
            'id' => \craftcom\id\Module::class,
        ],
    ],
    '.com' => [
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
            'session' => function() {
                $stateKeyPrefix = md5('Craft.'.craft\web\Session::class.'.'.Craft::$app->id);

                /** @var yii\redis\Session $session */
                $session = Craft::createObject([
                    'class' => yii\redis\Session::class,
                    'flashParam' => $stateKeyPrefix.'__flash',
                    'name' => Craft::$app->getConfig()->getGeneral()->phpSessionName,
                    'cookieParams' => Craft::cookieConfig(),
                ]);

                $session->attachBehaviors([craft\behaviors\SessionBehavior::class]);
                $session->authAccessParam = $stateKeyPrefix.'__auth_access';
                return $session;
            },
        ],
    ]
];
