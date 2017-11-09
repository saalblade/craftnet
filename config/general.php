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
        'defaultCookieDomain' => '.craftcms.com',
        'trustedHosts' => [
            '66.39.160.110'
        ],
    ],
    'dev' => [
        'devMode' => true,
        'allowAutoUpdates' => true,
        'defaultCookieDomain' => '.craftcms.dev',
    ]
];
