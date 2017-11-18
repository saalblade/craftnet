<?php

namespace craftcom\console\controllers;

use craftcom\Module;
use yii\console\Controller;

/**
 * @property Module $module
 */
class DumpJsonController extends Controller
{
    public $queue = false;

    public function options($actionID)
    {
        $options = parent::options($actionID);
        $options[] = 'queue';
        return $options;
    }

    public function optionAliases()
    {
        $aliases = parent::optionAliases();
        $aliases['q'] = 'queue';
        return $aliases;
    }

    public function actionIndex()
    {
        $this->module->getJsonDumper()->dump($this->queue);
    }
}
