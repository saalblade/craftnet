<?php

namespace craftnet;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\services\OrderAdjustments;
use craft\commerce\services\Purchasables;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\UserEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\services\Users;
use craft\services\Utilities;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craftnet\cms\CmsEdition;
use craftnet\cms\CmsLicenseManager;
use craftnet\composer\JsonDumper;
use craftnet\composer\PackageManager;
use craftnet\developers\UserBehavior;
use craftnet\developers\UserQueryBehavior;
use craftnet\fields\Plugins;
use craftnet\orders\OrderBehavior;
use craftnet\plugins\PluginEdition;
use craftnet\plugins\PluginLicenseManager;
use craftnet\services\Oauth;
use craftnet\twigextensions\CraftIdTwigExtension;
use craftnet\utilities\UnavailablePlugins;
use yii\base\Event;

/**
 * @property CmsLicenseManager $cmsLicenseManager
 * @property JsonDumper $jsonDumper
 * @property Oauth $oauth
 * @property PackageManager $packageManager
 * @property PluginLicenseManager $pluginLicenseManager
 */
class Module extends \yii\base\Module
{
    public function init()
    {
        Craft::setAlias('@craftnet', __DIR__);

        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->_initConsoleRequest();
        } else {
            $this->_initWebRequest();

            if ($request->getIsCpRequest()) {
                $this->_initCpRequest();
            }
        }

        Event::on(UserQuery::class, UserQuery::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = UserQueryBehavior::class;
        });

        Event::on(User::class, User::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = UserBehavior::class;
        });

        Event::on(Users::class, Users::EVENT_AFTER_ACTIVATE_USER, function(UserEvent $e) {
            // any unclaimed Craft/plugin licenses that were paid for with this email?
            $this->getCmsLicenseManager()->claimLicenses($e->user);
            $this->getPluginLicenseManager()->claimLicenses($e->user);
        });

        Event::on(Purchasables::class, Purchasables::EVENT_REGISTER_PURCHASABLE_ELEMENT_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = CmsEdition::class;
            $e->types[] = PluginEdition::class;
        });

        Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $e) {
            $e->types[] = EditionUpgradeDiscount::class;
        });

        Event::on(Order::class, Order::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = OrderBehavior::class;
        });

        Craft::$app->view->twig->addExtension(new CraftIdTwigExtension());

        parent::init();
    }

    /**
     * @return CmsLicenseManager
     */
    public function getCmsLicenseManager(): CmsLicenseManager
    {
        return $this->get('cmsLicenseManager');
    }

    /**
     * @return PluginLicenseManager
     */
    public function getPluginLicenseManager(): PluginLicenseManager
    {
        return $this->get('pluginLicenseManager');
    }

    /**
     * @return PackageManager
     */
    public function getPackageManager(): PackageManager
    {
        return $this->get('packageManager');
    }

    /**
     * @return JsonDumper
     */
    public function getJsonDumper(): JsonDumper
    {
        return $this->get('jsonDumper');
    }

    /**
     * @return Oauth
     */
    public function getOauth(): Oauth
    {
        return $this->get('oauth');
    }

    private function _initConsoleRequest()
    {
        $this->controllerNamespace = 'craftnet\\console\\controllers';
    }

    private function _initWebRequest()
    {
        $this->controllerNamespace = 'craftnet\\controllers';

        Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
    }

    private function _initCpRequest()
    {
        $this->controllerNamespace = 'craftnet\\controllers';

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $e) {
            $e->navItems[] = [
                'url' => 'plugins',
                'label' => 'Plugins',
                'icon' => 'plugin',
            ];
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $e) {
            $e->rules = array_merge($e->rules, [
                'plugins' => ['template' => 'craftnet/plugins/_index'],
                'plugins/new' => 'craftnet/plugins/edit',
                'plugins/<pluginId:\d+><slug:(?:-[^\/]*)?>' => 'craftnet/plugins/edit',
            ]);
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['craftnet'] = __DIR__.'/templates';
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = Plugins::class;
        });

        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = UnavailablePlugins::class;
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $e) {
            $e->permissions['Craftcom'] = [
                'craftnet:managePlugins' => [
                    'label' => 'Manage plugins',
                ],
            ];
        });
    }
}
