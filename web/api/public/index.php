<?php
/**
 * Craft web bootstrap file
 */

define('CRAFT_BASE_PATH', dirname(__DIR__));
define('CRAFT_VENDOR_PATH', dirname(CRAFT_BASE_PATH, 2).'/vendor');

// Composer autoloader
require_once CRAFT_VENDOR_PATH.'/autoload.php';

// dotenv
if (getenv('DB_SERVER') !== false) {
    $dotenv = new Dotenv\Dotenv(dirname(CRAFT_VENDOR_PATH));
    $dotenv->load();
}

$app = require CRAFT_VENDOR_PATH.'/craftcms/cms/bootstrap/web.php';
$app->run();
