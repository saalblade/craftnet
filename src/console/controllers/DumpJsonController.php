<?php

namespace craftcom\console\controllers;

use craftcom\composer\Package;
use craftcom\Module;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * @property Module $module
 */
class DumpJsonController extends Controller
{
    public function actionIndex()
    {
        $this->module->getJsonDumper()->dump();
    }
}
