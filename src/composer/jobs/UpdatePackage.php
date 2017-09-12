<?php

namespace craftcom\composer\jobs;

use Craft;
use craft\queue\BaseJob;
use craftcom\composer\Module;

class UpdatePackage extends BaseJob
{
    public $name;
    public $force = false;
    public $skipIfRecentlyUpdated = true;

    public function execute($queue)
    {
        /** @var Module $module */
        $module = Craft::$app->getModule('composer');
        $packageManager = $module->getPackageManager();

        if ($this->skipIfRecentlyUpdated && $packageManager->packageUpdatedWithin($this->name, 60 * 5)) {
            return;
        }

        $packageManager->updatePackage($this->name, $this->force);
    }
}
