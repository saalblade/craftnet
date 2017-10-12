<?php

namespace craftcom\id\controllers;

use Craft;
use craft\records\OAuthToken;
use Exception;
use yii\web\Response;
use craft\helpers\Db;

/**
 * Class AppsController
 *
 * @package craftcom\id\controllers
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
    public function actionConnect($providerHandle)
    {
        Craft::$app->getSession()->set('connectProviderHandle', $providerHandle);

        $providerConfig = $this->_getProviderConfig($providerHandle);
        $provider = $this->_getProvider($providerHandle);

        $options = [
            'scope' => $providerConfig['scope'],
        ];
        $authUrl = $provider->getAuthorizationUrl($options);
        Craft::$app->getSession()->set('oauth2state', $provider->getState());

        return $this->redirect($authUrl);
    }

    /**
     * OAuth callback.
     *
     * @return Response
     * @throws Exception
     */
    public function actionCallback()
    {
        $providerHandle = Craft::$app->getSession()->get('connectProviderHandle');
        $providerConfig = $this->_getProviderConfig($providerHandle);
        $provider = $this->_getProvider($providerHandle);

        $code = Craft::$app->getRequest()->getParam('code');
        $state = Craft::$app->getRequest()->getParam('state');

        if (!$code || !$state) {
            Craft::$app->getSession()->remove('connectProviderHandle');
            Craft::error("Either the code or the oauth2state param was missing in the {$providerConfig['class']} callback.", __METHOD__);
            throw new \Exception('There was a problem getting an authorzation token.');
        }

        if ($state !== Craft::$app->getSession()->get('oauth2state')) {
            Craft::$app->getSession()->remove('connectProviderHandle');
            Craft::error("oauth2state was missing in session from the {$providerConfig['class']} callback.", __METHOD__);
            throw new \Exception('There was a problem getting an authorzation token.');
        }

        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);

            Craft::$app->getSession()->remove('connectProviderHandle');

        } catch (\Exception $e) {
            Craft::error('There was a problem getting an authorization token.', __METHOD__);
            return $this->redirect($this->_connectUri);
        }

        $currentUser = Craft::$app->getUser()->getIdentity();
        $existingToken = $this->_getAuthTokenByUserId($providerConfig['class'], $currentUser->id);

        // No previous acces token, create a new one.
        if (!$existingToken) {
            $tokenRecord = new OAuthToken();
            $tokenRecord->userId = $currentUser->id;
            $tokenRecord->provider = $providerConfig['class'];

        } else {
            // A previous one, let's update it.
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('accessToken', $existingToken))
                ->andWhere(Db::parseParam('provider', $providerConfig['class']))
                ->one();
        }

        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();


        // Apps

        $apps = $this->getApps();

        return $this->renderTemplate('apps/callback', [
            'apps' => $apps
        ]);
    }

    /**
     * OAuth disconnect.
     *
     * @return Response
     */
    public function actionDisconnect()
    {
        $providerHandle = Craft::$app->getRequest()->getBodyParam('providerHandle');
        $providerConfig = $this->_getProviderConfig($providerHandle);

        $currentUser = Craft::$app->getUser()->getIdentity();

        Craft::$app->getDb()->createCommand()
            ->delete('oauthtokens', ['userId' => $currentUser->id, 'provider' => $providerConfig['class']])
            ->execute();

        return $this->asJson(['success' => true]);
    }
}
