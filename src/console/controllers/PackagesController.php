<?php

namespace craftcom\console\controllers;

use craftcom\composer\Package;
use craftcom\Module;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * @property Module $module
 */
class PackagesController extends Controller
{
    public $force = false;
    public $managed = false;
    public $repository;
    public $type = 'library';
    public $queue = false;

    public function options($actionID)
    {
        $options = parent::options($actionID);

        switch ($actionID) {
            case 'add':
                $options[] = 'managed';
                $options[] = 'repository';
                $options[] = 'type';
                break;
            case 'update':
                $options[] = 'force';
                break;
            case 'update-deps':
                $options[] = 'queue';
        }

        return $options;
    }

    public function optionAliases()
    {
        $aliases = parent::optionAliases();
        $aliases['f'] = 'force';
        $aliases['m'] = 'managed';
        $aliases['r'] = 'repository';
        $aliases['t'] = 'type';
        $aliases['q'] = 'queue';
        return $aliases;
    }

    public function actionAdd(string $name)
    {
        $packageManager = $this->module->getPackageManager();
        $package = new Package([
            'name' => $name,
            'type' => $this->type,
            'repository' => $this->repository,
            'managed' => $this->managed,
        ]);
        $packageManager->savePackage($package);
        Console::output("Done adding {$name}");
        if ($this->confirm('Update its versions now?')) {
            $packageManager->updatePackage($name);
        }
    }

    public function actionRemove(string $name)
    {
        if ($this->confirm("Are you sure you want to remove {$name}?")) {
            $this->module->getPackageManager()->removePackage($name);
            Console::output("{$name} removed");
        }
    }

    public function actionUpdate(string $name)
    {
        $this->module->getPackageManager()->updatePackage($name, $this->force);
    }

    public function actionUpdateDeps()
    {
        $this->module->getPackageManager()->updateDeps($this->queue);
    }
}
