<?php

/** @var \craft\web\Application $app */

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use craft\db\Query;
use craft\helpers\ArrayHelper;
use craftnet\Module;
use craftnet\plugins\Plugin;

$app = require dirname(__DIR__).'/bootstrap.php';
$packageManager = Module::getInstance()->getPackageManager();



$plugin = Plugin::find()
    ->handle('two-factor-authentication')
    ->withLatestReleaseInfo(true, null, 'beta', true)
    ->asArray()
    ->one();

Craft::dd($plugin);
