<?php

namespace craftnet;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\events\PdfEvent;
use craft\commerce\services\OrderAdjustments;
use craft\commerce\services\Pdf;
use craft\commerce\services\Purchasables;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterEmailMessagesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\events\UserEvent;
use craft\models\SystemMessage;
use craft\services\Fields;
use craft\services\SystemMessages;
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
use craftnet\invoices\InvoiceManager;
use craftnet\orders\OrderBehavior;
use craftnet\orders\PdfRenderer;
use craftnet\plugins\PluginEdition;
use craftnet\plugins\PluginLicenseManager;
use craftnet\services\Oauth;
use craftnet\utilities\SalesReport;
use craftnet\utilities\UnavailablePlugins;
use yii\base\Event;

/**
 * @property CmsLicenseManager $cmsLicenseManager
 * @property InvoiceManager $invoiceManager
 * @property JsonDumper $jsonDumper
 * @property Oauth $oauth
 * @property PackageManager $packageManager
 * @property PluginLicenseManager $pluginLicenseManager
 */
class Module extends \yii\base\Module
{
    const MESSAGE_KEY_RECEIPT = 'craftnet_receipt';
    const MESSAGE_KEY_VERIFY = 'verify_email';
    const MESSAGE_KEY_DEVELOPER_SALE = 'developer_sale';

    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftnet', __DIR__);

        // define custom behaviors
        Event::on(UserQuery::class, UserQuery::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = UserQueryBehavior::class;
        });
        Event::on(User::class, User::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = UserBehavior::class;
        });
        Event::on(Order::class, Order::EVENT_DEFINE_BEHAVIORS, function(DefineBehaviorsEvent $e) {
            $e->behaviors[] = OrderBehavior::class;
        });

        // register custom component types
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = Plugins::class;
        });
        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = UnavailablePlugins::class;
            $e->types[] = SalesReport::class;
        });
        Event::on(Purchasables::class, Purchasables::EVENT_REGISTER_PURCHASABLE_ELEMENT_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = CmsEdition::class;
            $e->types[] = PluginEdition::class;
        });
        Event::on(OrderAdjustments::class, OrderAdjustments::EVENT_REGISTER_ORDER_ADJUSTERS, function(RegisterComponentTypesEvent $e) {
            $e->types[] = EditionUpgradeDiscount::class;
        });

        // register our custom receipt system message
        Event::on(SystemMessages::class, SystemMessages::EVENT_REGISTER_MESSAGES, function(RegisterEmailMessagesEvent $e) {
            $e->messages[] = new SystemMessage([
                'key' => self::MESSAGE_KEY_RECEIPT,
                'heading' => 'When someone places an order:',
                'subject' => 'Your receipt from {{ fromName }}',
                'body' => file_get_contents(__DIR__.'/emails/receipt.txt'),
            ]);
            $e->messages[] = new SystemMessage([
                'key' => self::MESSAGE_KEY_VERIFY,
                'heading' => 'When someone wants to claim licenses by an email address:',
                'subject' => 'Verify your email',
                'body' => file_get_contents(__DIR__.'/emails/verify.txt'),
            ]);
            $e->messages[] = new SystemMessage([
                'key' => self::MESSAGE_KEY_DEVELOPER_SALE,
                'heading' => 'When a plugin developer makes a sale:',
                'subject' => 'Craft Plugin Store Sale',
                'body' => file_get_contents(__DIR__.'/emails/developer_sale.txt'),
            ]);
        });

        // claim Craft/plugin licenses after user activation
        Event::on(Users::class, Users::EVENT_AFTER_ACTIVATE_USER, function(UserEvent $e) {
            $this->getCmsLicenseManager()->claimLicenses($e->user);
            $this->getPluginLicenseManager()->claimLicenses($e->user);
        });

        // provide custom order receipt PDF generation
        Event::on(Pdf::class, Pdf::EVENT_BEFORE_RENDER_PDF, function(PdfEvent $e) {
            $e->pdf = (new PdfRenderer())->render($e->order);
        });

        // request type-specific stuff
        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'craftnet\\console\\controllers';
        } else {
            $this->controllerNamespace = 'craftnet\\controllers';

            if ($request->getIsCpRequest()) {
                $this->_initCpRequest();
            } else {
                $this->_initSiteRequest();
            }
        }

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
     * @return InvoiceManager
     */
    public function getInvoiceManager(): InvoiceManager
    {
        return $this->get('invoiceManager');
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

    private function _initCpRequest()
    {
        $this->controllerNamespace = 'craftnet\\controllers';

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $e) {
            $e->navItems[] = [
                'url' => 'plugins',
                'label' => 'Plugins',
                'icon' => 'plugin',
            ];

            $e->navItems[] = [
                'url' => 'partners',
                'label' => 'Partners',
                'icon' => 'users',
            ];
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $e) {
            $e->rules = array_merge($e->rules, [
                'plugins' => ['template' => 'craftnet/plugins/_index'],
                'plugins/new' => 'craftnet/plugins/edit',
                'plugins/<pluginId:\d+><slug:(?:-[^\/]*)?>' => 'craftnet/plugins/edit',
                'partners' => ['template' => 'craftnet/partners/_index'],
                'partners/new' => 'craftnet/partners/edit',
                'partners/<partnerId:\d+><slug:(?:-[^\/]*)?>' => 'craftnet/partners/edit',
            ]);
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['craftnet'] = __DIR__.'/templates';
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $e) {
            $e->permissions['Craftcom'] = [
                'craftnet:managePlugins' => [
                    'label' => 'Manage plugins',
                ],
            ];
        });
    }

    private function _initSiteRequest()
    {
        Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
    }
}
