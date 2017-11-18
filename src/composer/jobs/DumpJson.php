<?php

namespace craftcom\composer\jobs;

use craft\queue\BaseJob;
use craftcom\Module;

class DumpJson extends BaseJob
{
    public function execute($queue)
    {
        Module::getInstance()->getJsonDumper()->dump();
    }

    protected function defaultDescription()
    {
        return 'Dump Composer repo JSON';
    }
}
