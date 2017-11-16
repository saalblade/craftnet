<?php

namespace craftcom\controllers\id;

use Craft;
use craft\records\OAuthToken;
use craftcom\Module;
use Exception;
use yii\web\Response;
use craft\helpers\Db;

/**
 * Class AppsController
 *
 * @package craftcom\controllers\id
 */
class AppsController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_connectUri = 'apps/connect';

    // Public Methods
    // =========================================================================

    /**
     * OAuth connect.
     *
     * @return Response
     */
    public function actionConnect($appTypeHandle): Response
    {
        Craft::$app->getSession()->set('connectAppTypeHandle', $appTypeHandle);

        $oauthService = Module::getInstance()->getOauth();
        $appTypeConfig = $oauthService->getAppTypeConfig($appTypeHandle);
        $oauthProvider = $oauthService->getAppTypeOauthProvider($appTypeHandle);

        $options = [
            'scope' => $appTypeConfig['scope'],
        ];

        $authUrl = $oauthProvider->getAuthorizationUrl($options);
        Craft::$app->getSession()->set('oauth2state', $oauthProvider->getState());

        return $this->redirect($authUrl);
    }

    /**
     * OAuth callback.
     *
     * @return Response
     * @throws Exception
     */
    public function actionCallback(): Response
    {
        $appTypeHandle = Craft::$app->getSession()->get('connectAppTypeHandle');
        $oauthService = Module::getInstance()->getOauth();
        $appTypeConfig = $oauthService->getAppTypeConfig($appTypeHandle);
        $oauthProvider = $oauthService->getAppTypeOauthProvider($appTypeHandle);

        $code = Craft::$app->getRequest()->getParam('code');
        $state = Craft::$app->getRequest()->getParam('state');

        if (!$code || !$state) {
            Craft::$app->getSession()->remove('connectAppTypeHandle');
            Craft::error("Either the code or the oauth2state param was missing in the {$appTypeConfig['class']} callback.", __METHOD__);
            throw new \Exception('There was a problem getting an authorzation token.');
        }

        if ($state !== Craft::$app->getSession()->get('oauth2state')) {
            Craft::$app->getSession()->remove('connectAppTypeHandle');
            Craft::error("oauth2state was missing in session from the {$appTypeConfig['class']} callback.", __METHOD__);
            throw new \Exception('There was a problem getting an authorzation token.');
        }

        try {
            $accessToken = $oauthProvider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);

            Craft::$app->getSession()->remove('connectAppTypeHandle');

        } catch (\Exception $e) {
            Craft::error('There was a problem getting an authorization token.', __METHOD__);
            return $this->redirect($this->_connectUri);
        }

        $currentUser = Craft::$app->getUser()->getIdentity();
        $existingToken = Module::getInstance()->getOauth()->getAuthTokenByUserId($appTypeConfig['class'], $currentUser->id);

        // No previous acces token, create a new one.
        if (!$existingToken) {
            $tokenRecord = new OAuthToken();
            $tokenRecord->userId = $currentUser->id;
            $tokenRecord->provider = $appTypeConfig['class'];

        } else {
            // A previous one, let's update it.
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('accessToken', $existingToken))
                ->andWhere(Db::parseParam('provider', $appTypeConfig['class']))
                ->one();
        }

        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();

        // Apps
        $apps = $oauthService->getApps();

        return $this->renderTemplate('apps/callback', [
            'apps' => $apps
        ]);
    }

    /**
     * OAuth disconnect.
     *
     * @return Response
     */
    public function actionDisconnect(): Response
    {
        $appTypeHandle = Craft::$app->getRequest()->getBodyParam('appTypeHandle');
        $appTypeConfig = Module::getInstance()->getOauth()->getAppTypeConfig($appTypeHandle);

        Module::getInstance()->getOauth()->deleteAccessToken(Craft::$app->getUser()->getIdentity()->id, $appTypeConfig['class']);

        return $this->asJson(['success' => true]);
    }
}
