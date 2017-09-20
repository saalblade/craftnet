<?php
/**
 * Craft web bootstrap file
 */

switch($_SERVER['HTTP_HOST']) {
    case 'api.craftcms.com':
    case 'api.craftcms.dev':
        define('CRAFT_SITE', 'api');
        break;
    case 'composer.craftcms.com':
    case 'composer.craftcms.dev':
        define('CRAFT_SITE', 'composer');
        break;
    case 'id.craftcms.com':
    case 'id.craftcms.dev':
        define('CRAFT_SITE', 'craftId');
        break;
    case 'queue.craftcms.com':
    case 'queue.craftcms.dev':
        define('CRAFT_SITE', 'queue');
        break;
}

define('CRAFT_BASE_PATH', __DIR__);
define('CRAFT_VENDOR_PATH', CRAFT_BASE_PATH.'/vendor');

// Composer autoloader
require_once CRAFT_VENDOR_PATH.'/autoload.php';

// dotenv
if (file_exists(CRAFT_BASE_PATH.'/.env')) {
    $dotenv = new Dotenv\Dotenv(CRAFT_BASE_PATH);
    $dotenv->load();
} else {
    // Only define this on AWS
    define('CRAFT_STORAGE_PATH', '/efs/storage');
}

define('CRAFT_ENVIRONMENT', getenv('CRAFT_ENV') ?: 'prod');

return require CRAFT_VENDOR_PATH.'/craftcms/cms/bootstrap/web.php';
