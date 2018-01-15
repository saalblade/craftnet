<?php

return [
    '*' => [
        'stripePublishableKey' => getenv('STRIPE_PUBLISHABLE_KEY'),
        'stripeSecretKey' => getenv('STRIPE_SECRET_KEY'),
        'stripeClientId' => getenv('STRIPE_CLIENT_ID'),
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
            'encryptionKey' => getenv('OAUTH_ENC_KEY'),
            'scopes' => [
                'purchasePlugins' => "Purchase plugins",
                'existingPlugins' => "List existing plugins",
                'transferPluginLicense' => "Transfer plugin license",
                'deassociatePluginLicense' => "Deassociate plugin license",
            ]
        ],
    ],
    'prod' => [
        'craftIdUrl' => 'https://id.craftcms.com',
        'enablePluginStoreCache' => true,
    ],
    'dev' => [
        'craftIdUrl' => 'https://id.craftcms.test',
        'enablePluginStoreCache' => false,
    ]
];
