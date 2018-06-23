<?php

namespace craftnet\composer\jobs;

use Craft;
use craft\queue\BaseJob;
use craftnet\Module;

class DumpJson extends BaseJob
{
    public function execute($queue)
    {
        Craft::info('Executing DumpJson job.', __METHOD__);
        Module::getInstance()->getJsonDumper()->dump();
    }

    protected function defaultDescription()
    {
        return 'Dump Composer repo JSON';
    }
}
