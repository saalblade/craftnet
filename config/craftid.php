<?php

return [
    '*' => [
        'stripeClientId' => 'ca_2b3yXOngHtKxb4cDEGHeCMhrNwXyWvu5',
        'stripeClientSecret' => 'sk_test_FgnfF68q9L8Hp3RRDETaJefc',
        'enablePluginStoreCache' => true,
        'oauthServer' => [
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
    'dev' => [
        'enablePluginStoreCache' => false,
    ]
];
