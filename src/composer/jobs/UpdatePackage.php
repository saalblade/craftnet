<?php

namespace craftcom\composer\jobs;

use craft\queue\BaseJob;
use craftcom\Module;

class UpdatePackage extends BaseJob
{
    public $name;
    public $force = false;
    public $skipIfRecentlyUpdated = true;

    public function execute($queue)
    {
        $packageManager = Module::getInstance()->getPackageManager();

        if ($this->skipIfRecentlyUpdated && $packageManager->packageUpdatedWithin($this->name, 60 * 5)) {
            return;
        }

        $packageManager->updatePackage($this->name, $this->force);
    }
}
