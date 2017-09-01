<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 */

return [
    '*' => [
        'siteUrl' => null,
        'omitScriptNameInUrls' => true,
        'cpTrigger' => 'ramos',
        'imageDriver' => 'imagick',
        'preventUserEnumeration' => true,
        'validationKey' => 'y56B>Ck7"<7k?BbVE>mY=b#cwBq]$JEp',

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
            'privateKey' => __DIR__. '/keys/private.key',
            'publicKey' => __DIR__. '/keys/public.key',
            'encryptionKey' => 'j3wsUhGQtKCTnAiYdMHz2oCqyv3pYron',
            'scopes' => [
                'purchasePlugins' => "Purchase plugins",
                'existingPlugins' => "List existing plugins",
                'transferPluginLicense' => "Transfer plugin license",
                'deassociatePluginLicense' => "Deassociate plugin license",
            ]
        ]
    ],
    'prod' => [
        'allowAutoUpdates' => false,
        'devMode' => true,
        'craftApiUrl' => 'https://api.craftcms.com/v1',
    ],
    'dev' => [
        'devMode' => true,
        'allowAutoUpdates' => true,
        'craftApiUrl' => 'https://api.craftcms.dev/v1',
    ]
];
