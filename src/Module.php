<?php

namespace craftcom;

use Craft;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use craft\web\View;
use craftcom\composer\PackageManager;
use yii\base\Event;

/**
 * @property PackageManager $packageManager
 */
class Module extends \yii\base\Module
{
    public function init()
    {
        Craft::setAlias('@craftcom', __DIR__);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'craftcom\\console\\controllers';
        }

        parent::init();
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager(): PackageManager
    {
        return $this->get('packageManager');
    }
}
