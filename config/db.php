<?php
/**
 * Database Configuration
 *
 * All of your system's database connection settings go in here. You can see a
 * list of the available settings in vendor/craftcms/cms/src/config/DbConfig.php.
 */

return [
    'driver' => craft\config\DbConfig::DRIVER_PGSQL,
    'server' => getenv('DB_SERVER') ?: $_SERVER['DB_SERVER'],
    'user' => getenv('DB_USER') ?: $_SERVER['DB_USER'],
    'password' => getenv('DB_PASSWORD') ?: $_SERVER['DB_PASSWORD'],
    'database' => getenv('DB_DATABASE') ?: $_SERVER['DB_DATABASE'],
    'schema' => getenv('DB_SCHEMA') ?: 'public',
    'tablePrefix' => getenv('DB_TABLE_PREFIX') ?: ''
];
