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
        'securityKey' => getenv('CRAFT_SECURITY_KEY') ?: $_SERVER['CRAFT_SECURITY_KEY'],
        'csrfTokenName' => 'CRAFTCOM_CSRF_TOKEN',
        'phpSessionName' => 'CraftComSessionId',
    ],
    'prod' => [
        'allowAutoUpdates' => false,
        'devMode' => isset($_REQUEST['secret']) && $_REQUEST['secret'] == 'mgt3md9snyd2' ? true : false,
        'craftApiUrl' => 'https://api.craftcms.com/v1',
        'defaultCookieDomain' => '.craftcms.com'
    ],
    'dev' => [
        'devMode' => true,
        'allowAutoUpdates' => true,
        'craftApiUrl' => 'http://api.craftcms.dev/v1',
        'defaultCookieDomain' => '.craftcms.dev',
        'enablePluginStoreCache' => false,
    ]
];
