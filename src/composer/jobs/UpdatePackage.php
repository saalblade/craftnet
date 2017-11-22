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

        Craft::error('Executing job for '.$this->name.'.', __METHOD__);

        if ($this->skipIfRecentlyUpdated && $packageManager->packageUpdatedWithin($this->name, 60 * 5)) {
            Craft::error('Skipping job for '.$this->name.'. Too soon, man... too soon.', __METHOD__);
            return;
        }

        $packageManager->updatePackage($this->name, $this->force);

        Craft::error('Executed job for '.$this->name.'.', __METHOD__);
    }

    protected function defaultDescription()
    {
        return 'Update '.$this->name;
    }
}
