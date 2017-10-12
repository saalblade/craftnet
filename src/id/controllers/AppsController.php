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
use craft\web\Controller;

/**
 * Class AppsController
 *
 * @package craftcom\id\controllers
 */
class AppsController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @var string
     */
    private $_connectUri = 'apps/connect';

    /**
     * @var array
     */
    private $providers = [
        'github' => [
            'class' => 'Github',
            'nsClass' => 'League\OAuth2\Client\Provider\Github',
            'clientIdKey' => 'GITHUB_APP_CLIENT_ID',
            'clientSecretKey' => 'GITHUB_APP_CLIENT_SECRET',
            'scope' => ['user:email', 'write:repo_hook'],
        ],
        'bitbucket' => [
            'class' => 'Bitbucket',
            'nsClass' => 'Stevenmaguire\OAuth2\Client\Provider\Bitbucket',
            'clientIdKey' => 'BITBUCKET_APP_CLIENT_ID',
            'clientSecretKey' => 'BITBUCKET_APP_CLIENT_SECRET',
            'scope' => 'account',
        ],
    ];

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
     * @throws GithubIdentityProviderException
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

        $apps = [];

        foreach($this->providers as $handle => $config) {
            $appProvider = $this->_getProvider($handle);
            $token = $this->_getOauthTokenByUserId($config['class'], $currentUser->id);
            if ($token) {
                $options = [
                    'access_token' => $token['accessToken'],
                ];

                if(isset($token['expiresIn'])) {
                    $options['expires_in'] = $token['expiresIn'];
                }

                $accessToken = new AccessToken($options);

                $account = $appProvider->getResourceOwner($accessToken);

                $apps[$handle] = [
                    'token' => $token,
                    'account' => $account->toArray()
                ];
            }
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
        $providerHandle = Craft::$app->getRequest()->getBodyParam('providerHandle');
        $providerConfig = $this->_getProviderConfig($providerHandle);

        $currentUser = Craft::$app->getUser()->getIdentity();

        Craft::$app->getDb()->createCommand()
            ->delete('oauthtokens', ['userId' => $currentUser->id, 'provider' => $providerConfig['class']])
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
    private function _getAuthTokenByUserId($providerClass, int $userId)
    {
        return (new Query())
            ->select(['accessToken'])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId, 'provider' => $providerClass])
            ->scalar();
    }

    /**
     * @param int $userId
     *
     * @return false|null|string
     */
    private function _getOauthTokenByUserId($providerClass, int $userId)
    {
        return (new Query())
            ->select([
                'id',
                'userId',
                'provider',
                'accessToken',
                'tokenType',
                'expiresIn',
                'expiryDate',
                'refreshToken',
            ])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId, 'provider' => $providerClass])
            ->one();
    }

    /**
     * @return Github
     */
    private function _getProvider($providerHandle)
    {
        $config = $this->_getProviderConfig($providerHandle);

        if($config) {
            return new $config['nsClass']([
                'clientId' => isset($_SERVER[$config['clientIdKey']]) ? $_SERVER[$config['clientIdKey']] : getenv($config['clientIdKey']),
                'clientSecret' => isset($_SERVER[$config['clientSecretKey']]) ? $_SERVER[$config['clientSecretKey']] : getenv($config['clientSecretKey']),
                'redirectUri' => 'http://id.craftcms.dev/apps/callback'
            ]);
        }
    }

    /**
     * @return Github
     */
    private function _getProviderConfig($providerHandle)
    {
        if(isset($this->providers[$providerHandle])) {
            return $this->providers[$providerHandle];
        }
    }
}
