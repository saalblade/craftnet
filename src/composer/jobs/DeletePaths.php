<?php

namespace craftcom\composer\jobs;

use craft\helpers\FileHelper;
use craft\queue\BaseJob;

class DeletePaths extends BaseJob
{
    /**
     * @var string[]
     */
    public $paths;

    public function execute($queue)
    {
        foreach ($this->paths as $path) {
            if (file_exists($path)) {
                FileHelper::removeFile($path);
            }
        }
    }
}
