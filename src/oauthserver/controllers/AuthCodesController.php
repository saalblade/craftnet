<?php

namespace craftcom\oauthserver\controllers;

use Craft;
use craftcom\oauthserver\Module as OauthServer;
use craft\web\Controller;
use yii\web\Response;

/**
 * Class AuthCodesController
 *
 * @package craftcom\oauthserver\controllers
 */
class AuthCodesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $authCodes = OauthServer::getInstance()->getAuthCodes()->getAuthCodes();

        return $this->renderTemplate('oauth-server/auth-codes', [
            'authCodes' => $authCodes,
        ]);
    }

    /**
     * @return Response
     */
    public function actionDeleteAuthCode(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $accessTokenId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        OauthServer::getInstance()->getAuthCodes()->deleteAuthCodeById($accessTokenId);

        return $this->asJson(['success' => true]);
    }

    /**
     * @return Response
     */
    public function actionClearAuthCodes()
    {
        OauthServer::getInstance()->getAuthCodes()->clearAuthCodes();

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Auth codes cleared.'));

        return $this->redirect('oauth-server/auth-codes');
    }
}