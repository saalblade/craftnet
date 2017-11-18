<?php

namespace craftcom\console\controllers;

use craftcom\Module;
use yii\console\Controller;

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
