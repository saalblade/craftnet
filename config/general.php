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
        'backupOnUpdate' => false,
        'backupCommand' => 'PGPASSWORD="{password}" ' .
            'pg_dump ' .
            '--dbname={database} '.
            '--host={server} '.
            '--port={port} '.
            '--username={user} '.
            '--if-exists '.
            '--clean '.
            '--file="{file}" '.
            '--schema={schema} '.
            '--schema=apilog '.
            '--exclude-table-data \'{schema}.assetindexdata\' '.
            '--exclude-table-data \'{schema}.assettransformindex\' '.
            '--exclude-table-data \'{schema}.cache\' '.
            '--exclude-table-data \'{schema}.sessions\' '.
            '--exclude-table-data \'{schema}.templatecaches\' '.
            '--exclude-table-data \'{schema}.templatecachecriteria\' '.
            '--exclude-table-data \'{schema}.templatecacheelements\' ' .
            '--exclude-table-data \'apilog.logs\' ' .
            '--exclude-table-data \'apilog.request_cmslicenses\' ' .
            '--exclude-table-data \'apilog.request_errors\' ' .
            '--exclude-table-data \'apilog.request_pluginlicenses\' ' .
            '--exclude-table-data \'apilog.requests\'',
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
    'stage' => [
        'devMode' => isset($_REQUEST['secret']) && $_REQUEST['secret'] === getenv('DEV_MODE_SECRET'),
        'allowUpdates' => false,
        'siteUrl' => [
            'api' => 'https://staging.api.craftcms.com/',
            'composer' => 'https://composer.craftcms.com/',
            'craftId' => 'https://staging.id.craftcms.com/',
            'plugins' => 'https://plugins.craftcms.com/',
        ],
        'defaultCookieDomain' => '.craftcms.com',
        'baseCpUrl' => 'https://staging.id.craftcms.com/',
    ],
    'dev' => [
        'devMode' => true,
        'allowUpdates' => true,
        'testToEmailAddress' => getenv('TEST_EMAIL'),
        'siteUrl' => [
            'api' => 'https://api.craftcms.test/',
            'composer' => 'https://composer.craftcms.test/',
            'craftId' => 'https://id.craftcms.test/',
            'plugins' => 'https://plugins.craftcms.test/',
        ],
        'defaultCookieDomain' => '.craftcms.test',
        'baseCpUrl' => 'https://id.craftcms.test/',
    ]
];
