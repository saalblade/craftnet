<?php

namespace craftcom\oauthserver\controllers;

use Craft;
use craftcom\oauthserver\Module as OauthServer;
use craftcom\oauthserver\Module;
use craft\web\Controller;
use craftcom\oauthserver\server\Repositories\AccessTokenRepository;
use yii\web\Response;

/**
 * Class AccessTokensController
 *
 * @package craftcom\oauthserver\controllers
 */
class AccessTokensController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionIndex()
    {
        $accessTokens = OauthServer::getInstance()->getAccessTokens()->getAccessTokens();
        $revokedAccessTokens = OauthServer::getInstance()->getAccessTokens()->getRevokedAccessTokens();

        return $this->renderTemplate('oauth-server/access-tokens', [
            'accessTokens' => $accessTokens,
            'revokedAccessTokens' => $revokedAccessTokens,
        ]);
    }

    /**
     * @param $accessTokenId
     *
     * @return Response
     */
    public function actionEdit($accessTokenId)
    {
        $accessToken = Module::getInstance()->getAccessTokens()->getAccessTokenById($accessTokenId);
        $scopes = Module::getInstance()->getOauth()->getScopes();

        return $this->renderTemplate('oauth-server/access-tokens/_edit', [
            'accessTokenId' => $accessTokenId,
            'accessToken' => $accessToken,
            'scopes' => $scopes,
        ]);
    }

    /**
     * @param $accessTokenIdentifier
     *
     * @return Response
     */
    public function actionRevokeByIdentifier($accessTokenIdentifier)
    {
        $accessTokenRepository = new AccessTokenRepository();
        $accessTokenRepository->revokeAccessToken($accessTokenIdentifier);

        return $this->redirect('oauth-server/access-tokens');
    }

    /**
     * @return Response
     */
    public function actionDeleteAccessToken(): Response
    {
        $this->requirePostRequest();
        $this->requireAcceptsJson();

        $accessTokenId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        OauthServer::getInstance()->getAccessTokens()->deleteAccessTokenById($accessTokenId);

        return $this->asJson(['success' => true]);
    }

    /**
     * @return Response
     */
    public function actionClearAccessTokens()
    {
        OauthServer::getInstance()->getAccessTokens()->clearAccessTokens();

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Access tokens cleared.'));

        return $this->redirect('oauth-server/access-tokens');
    }
}