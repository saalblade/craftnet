<?php

namespace craftcom\composer\jobs;

use Craft;
use craft\queue\BaseJob;
use craftcom\Module;

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
