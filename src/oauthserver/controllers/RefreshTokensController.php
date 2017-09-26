<?php

namespace craftcom\oauthserver\controllers;

use Craft;
use craft\web\Controller;
use craftcom\oauthserver\Module as OauthServer;
use yii\web\Response;

/**
 * Class RefreshTokensController
 *
 * @package craftcom\oauthserver\controllers
 */
class RefreshTokensController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $refreshTokens = OauthServer::getInstance()->getRefreshTokens()->getRefreshTokens();

        return $this->renderTemplate('oauth-server/refresh-tokens', [
            'refreshTokens' => $refreshTokens,
        ]);
    }

    /**
     * @return Response
     */
    public function actionClearRefreshTokens()
    {
        OauthServer::getInstance()->getRefreshTokens()->clearRefreshTokens();

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Refresh tokens cleared.'));

        return $this->redirect('oauth-server/refresh-tokens');
    }
}
