<?php

return [
    '*' => [
        'bootstrap' => [
            'craftcom',
            'oauth-server'
        ],
        'modules' => [
            'api' => \craftcom\api\Module::class,
            'craftcom' => [
                'class' => \craftcom\Module::class,
                'components' => [
                    'packageManager' => \craftcom\composer\PackageManager::class,
                ]
            ],
            'id' => \craftcom\id\Module::class,
            'oauth-server' => [
                'class' => \craftcom\oauthserver\Module::class,
                'oauthServerConfig' => [
                    'accessTokenExpiry' => 'PT1H',
                    'refreshTokenExpiry' => 'P1M',
                    'authCodeExpiry' => 'P1M',
                    'clientApprovalTemplate' => 'oauth/clientApproval',
                    'enabledGrants' => [
                        'ClientCredentialsGrant',
                        'PasswordGrant',
                        'RefreshTokenGrant',
                        'ImplicitGrant',
                        'AuthCodeGrant',
                    ],
                    'grants' => [
                        'ClientCredentialsGrant' => 'Client Credentials Grant',
                        'PasswordGrant' => 'Password Grant',
                        'AuthCodeGrant' => 'Authorization Code Grant',
                        'ImplicitGrant' => 'Implicit Grant',
                        'RefreshTokenGrant' => 'Refresh Token Grant',
                    ],
                    'privateKey' => __DIR__.'/keys/oauth-server',
                    'publicKey' => __DIR__.'/keys/oauth-server.pub',
                    'encryptionKey' => 'j3wsUhGQtKCTnAiYdMHz2oCqyv3pYron',
                    'scopes' => [
                        'purchasePlugins' => "Purchase plugins",
                        'existingPlugins' => "List existing plugins",
                        'transferPluginLicense' => "Transfer plugin license",
                        'deassociatePluginLicense' => "Deassociate plugin license",
                    ]
                ],
            ],
            'q' => \craftcom\q\Module::class,
        ],
    ],
    'prod' => [
        'components' => [
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => 'craft.4qveoj.ng.0001.usw2.cache.amazonaws.com',
                'port' => 6379,
                'database' => 0,
            ],
            'cache' => [
                'class' => yii\redis\Cache::class,
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
