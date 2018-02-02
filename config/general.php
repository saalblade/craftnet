<?php
/**
 * General Configuration
 *
 * All of your system's general configuration settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/GeneralConfig.php.
 */

return [
    '*' => [
        'omitScriptNameInUrls' => true,
        'cpTrigger' => getenv('CRAFT_CP_TRIGGER'),
        'imageDriver' => 'imagick',
        'preventUserEnumeration' => true,
        'securityKey' => getenv('CRAFT_SECURITY_KEY'),
        'csrfTokenName' => 'CRAFTCOM_CSRF_TOKEN',
        'phpSessionName' => 'CraftComSessionId',
        'generateTransformsBeforePageLoad' => true,
    ],
    'prod' => [
        'allowUpdates' => false,
        'devMode' => isset($_REQUEST['secret']) && $_REQUEST['secret'] === getenv('DEV_MODE_SECRET'),
        'siteUrl' => [
            'api' => 'https://api.craftcms.com/',
            'composer' => 'https://composer.craftcms.com/',
            'craftId' => 'https://id.craftcms.com/',
            'plugins' => 'https://plugins.craftcms.com/',
        ],
        'defaultCookieDomain' => '.craftcms.com',
        'baseCpUrl' => 'https://id.craftcms.com/',
        'runQueueAutomatically' => false,
    ],
    'dev' => [
        'devMode' => true,
        'allowUpdates' => true,
        'siteUrl' => [
            'api' => 'https://api.craftcms.test/',
            'composer' => 'https://composer.craftcms.test/',
            'craftId' => 'https://id.craftcms.test/',
            'plugins' => 'https://plugins.craftcms.test/',
        ],
        'defaultCookieDomain' => '.craftcms.test',
        'baseCpUrl' => 'http://id.craftcms.test/',
    ]
];
