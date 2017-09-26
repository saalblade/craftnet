<?php

namespace craftcom;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craftcom\composer\PackageManager;
use craftcom\cp\fields\Plugins;
use yii\base\Event;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;

/**
 * @property PackageManager $packageManager
 */
class Module extends \yii\base\Module
{
    public function init()
    {
        Craft::setAlias('@craftcom', __DIR__);

        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->_initConsoleRequest();
        } else if ($request->getIsCpRequest()) {
            $this->_initCpRequest();
        } else {
            $this->_initWebRequest();
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

    private function _initConsoleRequest()
    {
        $this->controllerNamespace = 'craftcom\\console\\controllers';
    }

    private function _initCpRequest()
    {
        $this->controllerNamespace = 'craftcom\\cp\\controllers';

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $e) {
            $e->navItems[] = [
                'url' => 'plugins',
                'label' => 'Plugins',
                'icon' => 'plugin',
            ];
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $e) {
            $e->rules = array_merge($e->rules, [
                'plugins' => ['template' => 'craftcom/plugins/_index'],
                'plugins/new' => 'craftcom/plugins/edit',
                'plugins/<pluginId:\d+><slug:(?:-[^\/]*)?>' => 'craftcom/plugins/edit',
            ]);
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['craftcom'] = __DIR__.'/templates';
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = Plugins::class;
        });
    }

    private function _initWebRequest()
    {
        $this->controllerNamespace = 'craftcom\\cp\\controllers';
    }
}
