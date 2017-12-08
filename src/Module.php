<?php

namespace craftcom;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craftcom\behaviors\Developer;
use craftcom\composer\JsonDumper;
use craftcom\composer\PackageManager;
use craftcom\fields\Plugins;
use craftcom\services\Oauth;
use yii\base\Event;

/**
 * @property JsonDumper     $jsonDumper
 * @property Oauth          $oauth
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
        } else {
            $this->_initWebRequest();

            if ($request->getIsCpRequest()) {
                $this->_initCpRequest();
            }
        }

        Event::on(User::class, User::EVENT_INIT, function(Event $e) {
            /** @var User $user */
            $user = $e->sender;
            $user->attachBehavior('developer', Developer::class);
        });

        Event::on(User::class, User::EVENT_AFTER_SAVE, function(ModelEvent $e) {
            /** @var User $user */
            $user = $e->sender;
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
                !$currentUser->isInGroup('developers')
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
            }
        });

        parent::init();
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

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $e) {
            $e->permissions['Craftcom'] = [
                'craftcom:managePlugins' => [
                    'label' => 'Manage plugins',
                ],
            ];
        });
    }
}
