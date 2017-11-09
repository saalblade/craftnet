<?php

namespace craftcom\oauthserver;

use Craft;
use craft\events\RegisterCpNavItemsEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\Cp;
use craft\web\UrlManager;
use craft\web\View;
use craftcom\oauthserver\base\ModuleTrait;
use craftcom\oauthserver\models\Settings;
use yii\base\Event;

class Module extends \yii\base\Module
{
    use ModuleTrait;

    /**
     * @inheritdoc
     */
    public function init()
    {
        Craft::setAlias('@craftcom/oauthserver/controllers', __DIR__.'/controllers');

        $this->setComponents([
            'accessTokens' => \craftcom\oauthserver\services\AccessTokens::class,
            'authCodes' => \craftcom\oauthserver\services\AuthCodes::class,
            'clients' => \craftcom\oauthserver\services\Clients::class,
            'oauth' => \craftcom\oauthserver\services\Oauth::class,
            'refreshTokens' => \craftcom\oauthserver\services\RefreshTokens::class,
            'tokens' => \craftcom\oauthserver\services\Tokens::class,
        ]);

        Event::on(Cp::class, Cp::EVENT_REGISTER_CP_NAV_ITEMS, function(RegisterCpNavItemsEvent $event) {
            $event->navItems['oauthServer'] = [
                'label' => "OAuth Server",
                'url' => UrlHelper::cpUrl('oauth-server'),
            ];
        });

        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots[$this->id] = __DIR__.'/templates';
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $rules = [
                'oauth-server' => 'oauth-server/cp',
                'oauth-server/clients' => 'oauth-server/clients/index',
                'oauth-server/clients/<clientId:\d+>' => 'oauth-server/clients/edit',
                'oauth-server/clients/new' => 'oauth-server/clients/edit',
                'oauth-server/access-tokens' => 'oauth-server/access-tokens/index',
                'oauth-server/access-tokens/<accessTokenId:\d+>' => 'oauth-server/access-tokens/edit',
                'oauth-server/refresh-tokens' => 'oauth-server/refresh-tokens/index',
                'oauth-server/auth-codes' => 'oauth-server/auth-codes/index',
                'oauth-server/settings' => 'oauth-server/settings/index',
                'oauth-server/playground' => 'oauth-server/playground/index',
            ];

            $event->rules = array_merge($event->rules, $rules);
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $rules = [
                'oauth/login' => 'oauth-server/oauth/login',
                'oauth/authorize' => 'oauth-server/oauth/authorize',
                'oauth/access-token' => 'oauth-server/oauth/access-token',
                'oauth/revoke' => 'oauth-server/oauth/revoke',
            ];

            $event->rules = array_merge($event->rules, $rules);
        });

        parent::init();
    }

    public function getSettings()
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        return new Settings($craftIdConfig['oauthServer']);
    }
}
