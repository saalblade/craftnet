<?php

namespace craftcom\id\controllers;

use Craft;
use craft\records\OAuthToken;
use League\OAuth2\Client\Provider\Exception\GithubIdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use yii\web\Response;
use craft\helpers\Db;
use craft\db\Query;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class AppsController
 *
 * @package craftcom\id\controllers
 */
class AppsController extends BaseApiController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_scope = ['user:email', 'write:repo_hook'];

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
    public function actionConnect()
    {
        $provider = $this->_getProvider();

        $options = [
            'scope' => $this->_scope,
        ];

        $authUrl = $provider->getAuthorizationUrl($options);
        Craft::$app->getSession()->set('oauth2state', $provider->getState());

        return $this->redirect($authUrl);
    }

    /**
     * OAuth callback.
     *
     * @return Response
     * @throws GithubIdentityProviderException
     */
    public function actionCallback()
    {
        $code = Craft::$app->getRequest()->getParam('code');
        $state = Craft::$app->getRequest()->getParam('state');

        if (!$code || !$state) {
            Craft::error('Either the code or the oauth2state param was missing in the Github callback.', __METHOD__);
            throw new GithubIdentityProviderException('There was a problem getting an authorzation token.', __METHOD);
        }

        if ($state !== Craft::$app->getSession()->get('oauth2state')) {
            Craft::error('oauth2state was missing in session from the Github callback.', __METHOD__);
            throw new GithubIdentityProviderException('There was a problem getting an authorzation token.', __METHOD__);
        }

        $provider = $this->_getProvider();

        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            Craft::error('There was a problem getting an authorization token.', __METHOD__);
            return $this->redirect($this->_connectUri);
        }

        $currentUser = Craft::$app->getUser()->getIdentity();
        $existingToken = $this->_getAuthTokenByUserId($currentUser->id);

        // No previous acces token, create a new one.
        if (!$existingToken) {
            $tokenRecord = new OAuthToken();
            $tokenRecord->userId = $currentUser->id;
            $tokenRecord->provider = 'Github';

        } else {
            // A previous one, let's update it.
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('accessToken', $existingToken))
                ->andWhere(Db::parseParam('provider', 'Github'))
                ->one();
        }

        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();


        // Apps

        $apps = [];

        $githubAccessToken = $this->_getAuthTokenByUserId($currentUser->id);
        $githubToken = new AccessToken([
            'access_token' => $githubAccessToken
        ]);

        $provider = $this->_getProvider();
        $githubAccount = $provider->getResourceOwner($githubToken);

        if ($githubToken) {
            $apps['github'] = [
                'token' => $this->_getAuthTokenByUserId($currentUser->id),
                'account' => $githubAccount->toArray()
            ];
        }

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
        $currentUser = Craft::$app->getUser()->getIdentity();
        $userId = $currentUser->id;
        $provider = 'Github';

        Craft::$app->getDb()->createCommand()
            ->delete('oauthtokens', ['userId' => $userId, 'provider' => $provider])
            ->execute();

        return $this->asJson(['success' => true]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param int $userId
     *
     * @return false|null|string
     */
    private function _getAuthTokenByUserId(int $userId)
    {
        return (new Query())
            ->select(['accessToken'])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId, 'provider' => 'Github'])
            ->scalar();
    }

    /**
     * @return Github
     */
    private function _getProvider()
    {
        return new Github([
            'clientId' => isset($_SERVER['GITHUB_APP_CLIENT_ID']) ? $_SERVER['GITHUB_APP_CLIENT_ID'] : getenv('GITHUB_APP_CLIENT_ID'),
            'clientSecret' => isset($_SERVER['GITHUB_APP_CLIENT_SECRET']) ? $_SERVER['GITHUB_APP_CLIENT_SECRET'] : getenv('GITHUB_APP_CLIENT_SECRET'),
        ]);
    }
}
