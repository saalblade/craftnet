<?php

namespace craftcom\composer\jobs;

use craft\queue\BaseJob;
use craftcom\Module;

class UpdatePackage extends BaseJob
{
    public $name;
    public $force = false;
    public $dumpJson = false;

    public function execute($queue)
    {
        Module::getInstance()->getPackageManager()->updatePackage($this->name, $this->force, false, $this->dumpJson);
    }

    protected function defaultDescription()
    {
        return 'Update '.$this->name;
    }
}
