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
