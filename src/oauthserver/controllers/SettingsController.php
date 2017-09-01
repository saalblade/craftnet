<?php

namespace craftcom\oauthserver\controllers;

use Craft;
use craftcom\oauthserver\Module as OauthServer;
use craft\web\Controller;

/**
 * Class SettingsController
 *
 * @package craftcom\oauthserver\controllers
 */
class SettingsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $grants = OauthServer::getInstance()->getOauth()->getGrants(false);
        $scopes = OauthServer::getInstance()->getOauth()->getScopes();
        $expiries = OauthServer::getInstance()->getOauth()->getExpiries();
        $isSecure = Craft::$app->getRequest()->getIsSecureConnection();

        return $this->renderTemplate('oauth-server/settings', [
            'grants' => $grants,
            'scopes' => $scopes,
            'expiries' => $expiries,
            'isSecure' => $isSecure,
        ]);
    }
}