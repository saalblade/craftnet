<?php

namespace craftcom;

use Craft;
use craft\commerce\services\OrderAdjustments;
use craft\commerce\services\Purchasables;
use craft\elements\db\UserQuery;
use craft\elements\User;
use craft\events\ModelEvent;
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
use craftcom\behaviors\Developer;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicenseManager;
use craftcom\composer\JsonDumper;
use craftcom\composer\PackageManager;
use craftcom\fields\Plugins;
use craftcom\plugins\PluginEdition;
use craftcom\plugins\PluginLicenseManager;
use craftcom\services\Oauth;
use craftcom\twigextensions\CraftIdTwigExtension;
use craftcom\utilities\UnavailablePlugins;
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
        Craft::setAlias('@craftcom', __DIR__);

        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->_initConsoleRequest();
        } else {
            $this->_initWebRequest();

            if ($request->getIsCpRequest()) {
                $this->_initCpRequest();
            }
        }

        Event::on(UserQuery::class, UserQuery::EVENT_AFTER_PREPARE, function(Event $e) {
            /** @var UserQuery $query */
            $query = $e->sender;

            if (Craft::$app->getDb()->tableExists('craftcom_developers')) {
                $query->query->leftJoin('craftcom_developers developers', '[[developers.id]] = [[users.id]]');
                $query->subQuery->leftJoin('craftcom_developers developers', '[[developers.id]] = [[users.id]]');
                $query->addSelect([
                    'developers.country',
                    'developers.balance',
                    'developers.stripeAccessToken',
                    'developers.stripeAccount',
                    'developers.payPalEmail',
                ]);
            }
        });

        Event::on(User::class, User::EVENT_INIT, function(Event $e) {
            /** @var User $user */
            $user = $e->sender;
            $user->attachBehavior('developer', Developer::class);
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $e) {
            /** @var User|Developer $user */
            $user = $e->sender;
            $isDeveloper = $user->isInGroup('developers');
            $request = Craft::$app->getRequest();
            $currentUser = Craft::$app->getUser()->getIdentity();
            $userGroups = Craft::$app->getUserGroups();

            // If it's a front-end site POST request and they're not currently a developer, check to see if they've opted into developer features.
            if (
                $currentUser &&
                $currentUser->id == $user->id &&
                $request->getIsSiteRequest() &&
                $request->getIsPost() &&
                $request->getBodyParam('fields.enablePluginDeveloperFeatures') &&
                !$isDeveloper
            ) {
                // Get any existing group IDs.
                $existingGroups = $userGroups->getGroupsByUserId($currentUser->id);
                $groupIds = [];

                foreach ($existingGroups as $existingGroup) {
                    $groupIds[] = $existingGroup->id;
                }

                // Add the developer group.
                $groupIds[] = $userGroups->getGroupByHandle('developers')->id;

                Craft::$app->getUsers()->assignUserToGroups($currentUser->id, $groupIds);
                $isDeveloper = true;
            }

            $db = Craft::$app->getDb();

            if ($isDeveloper && $db->tableExists('craftcom_developers')) {
                $db->createCommand()
                    ->upsert('craftcom_developers', [
                        'id' => $user->id,
                    ], [
                        'country' => $user->country,
                        'stripeAccessToken' => $user->stripeAccessToken,
                        'stripeAccount' => $user->stripeAccount,
                        'payPalEmail' => $user->payPalEmail,
                    ], [], false)
                    ->execute();
            }
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
        $this->controllerNamespace = 'craftcom\\console\\controllers';
    }

    private function _initWebRequest()
    {
        $this->controllerNamespace = 'craftcom\\controllers';

        Craft::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
    }

    private function _initCpRequest()
    {
        $this->controllerNamespace = 'craftcom\\controllers';

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

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = Plugins::class;
        });

        Event::on(Utilities::class, Utilities::EVENT_REGISTER_UTILITY_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = UnavailablePlugins::class;
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $e) {
            $e->permissions['Craftcom'] = [
                'craftcom:managePlugins' => [
                    'label' => 'Manage plugins',
                ],
            ];
        });
    }
}
