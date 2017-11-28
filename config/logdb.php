<?php
/**
 * Database Configuration
 *
 * All of your system's database connection settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/DbConfig.php.
 */

return [
    'driver' => craft\config\DbConfig::DRIVER_PGSQL,
    'server' => getenv('LOG_DB_SERVER'),
    'user' => getenv('LOG_DB_USER'),
    'password' => getenv('LOG_DB_PASSWORD'),
    'database' => getenv('LOG_DB_DATABASE'),
    'schema' => getenv('LOG_DB_SCHEMA') ?: 'public',
    'tablePrefix' => getenv('LOG_DB_TABLE_PREFIX') ?: ''
];
