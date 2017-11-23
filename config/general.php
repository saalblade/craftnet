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
        'cpTrigger' => getenv('CRAFT_CP_TRIGGER'),
        'imageDriver' => 'imagick',
        'preventUserEnumeration' => true,
        'securityKey' => getenv('CRAFT_SECURITY_KEY'),
        'csrfTokenName' => 'CRAFTCOM_CSRF_TOKEN',
        'phpSessionName' => 'CraftComSessionId',
        'runQueueAutomatically' => false,
    ],
    'prod' => [
        'allowAutoUpdates' => false,
        'devMode' => isset($_REQUEST['secret']) && $_REQUEST['secret'] === getenv('DEV_MODE_SECRET'),
        'defaultCookieDomain' => '.craftcms.com',
        'baseCpUrl' => 'https://id.craftcms.com/',
    ],
    'dev' => [
        'devMode' => true,
        'allowAutoUpdates' => true,
        'defaultCookieDomain' => '.craftcms.dev',
        'baseCpUrl' => 'http://id.craftcms.dev/',
    ]
];
