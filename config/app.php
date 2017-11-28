<?php

use craftcom\services\Oauth;

return [
    '*' => [
        'bootstrap' => [
            'craftcom',
            'oauth-server',
            'queue',
        ],
        'modules' => [
            'craftcom' => [
                'class' => \craftcom\Module::class,
                'components' => [
                    'packageManager' => [
                        'class' => craftcom\composer\PackageManager::class,
                        'githubFallbackTokens' => getenv('GITHUB_FALLBACK_TOKENS'),
                        'requirePluginVcsTokens' => false,
                    ],
                    'jsonDumper' => [
                        'class' => \craftcom\composer\JsonDumper::class,
                        'composerWebroot' => getenv('COMPOSER_WEBROOT'),
                    ],
                    'oauth' => [
                        'class' => Oauth::class,
                        'appTypes' => [
                            Oauth::PROVIDER_GITHUB => [
                                'class' => 'Github',
                                'oauthClass' => League\OAuth2\Client\Provider\Github::class,
                                'clientIdKey' => getenv('GITHUB_APP_CLIENT_ID'),
                                'clientSecretKey' => getenv('GITHUB_APP_CLIENT_SECRET'),
                                'scope' => ['user:email', 'write:repo_hook', 'repo'],
                            ],
                            Oauth::PROVIDER_BITBUCKET => [
                                'class' => 'Bitbucket',
                                'oauthClass' => Stevenmaguire\OAuth2\Client\Provider\Bitbucket::class,
                                'clientIdKey' => getenv('BITBUCKET_APP_CLIENT_ID'),
                                'clientSecretKey' => getenv('BITBUCKET_APP_CLIENT_SECRET'),
                                'scope' => 'account',
                            ],
                        ]
                    ],
                ]
            ],
            'oauth-server' => [
                'class' => craftcom\oauthserver\Module::class,
            ],
        ],
    ],
    'prod' => [
        'components' => [
            'redis' => [
                'class' => yii\redis\Connection::class,
                'hostname' => getenv('ELASTICACHE_HOSTNAME'),
                'port' => getenv('ELASTICACHE_PORT'),
                'database' => 0,
            ],
            'cache' => [
                'class' => yii\redis\Cache::class,
                'redis' => [
                    'hostname' => getenv('ELASTICACHE_HOSTNAME'),
                    'port' => getenv('ELASTICACHE_PORT'),
                    'database' => 0,
                ],
            ],
            'queue' => [
                'class' => pixelandtonic\yii\queue\sqs\Queue::class,
                'url' => getenv('SQS_URL'),
                'client' => [
                    'region' => getenv('REGION'),
                    'version' => '2012-11-05',
                ]
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
