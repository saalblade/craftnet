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
    '.com' => [
        'allowAutoUpdates' => false,
        'devMode' => true,
    ],
    '.dev' => [
        'devMode' => true,
        'allowAutoUpdates' => true,
    ]
];
