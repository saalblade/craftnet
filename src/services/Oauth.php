<?php

namespace craftcom\services;

use Craft;
use craft\db\Query;
use craft\helpers\Json;
use craftcom\Module;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use yii\base\Component;
use yii\base\Exception;

class Oauth extends Component
{
    /**
     * @var array
     */
    public $appTypes = [];

    /**
     * @return array
     */
    public function getApps(): array
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $apps = [];

        foreach ($this->appTypes as $handle => $config) {
            $oauthProvider = $this->getAppTypeOauthProvider($handle);
            $token = $this->getOauthTokenByUserId($config['class'], $currentUser->id);

            if ($token) {
                $accessToken = $this->createAccessToken($token);
                $resourceOwner = $oauthProvider->getResourceOwner($accessToken);
                $account = $resourceOwner->toArray();

                $repositories = [];

                if ($handle === 'github') {
                    $response = Craft::createGuzzleClient()->request('GET', 'https://api.github.com/user/repos', [
                        'headers' => [
                            'Accept' => 'application/vnd.github.v3+json',
                            'Authorization' => 'token '.$accessToken->getToken(),
                        ],
                        'query' => [
                            'per_page' => 100
                        ]
                    ]);
                    $body = $response->getBody();
                    $contents = $body->getContents();
                    $repositories = Json::decode($contents);
                }

                $apps[$handle] = [
                    'token' => $token,
                    'account' => $account,
                    'repositories' => $repositories,
                ];
            }
        }

        return $apps;
    }

    /**
     * @return AbstractProvider
     */
    public function getAppTypeOauthProvider($appTypeHandle): AbstractProvider
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $config = $this->getAppTypeConfig($appTypeHandle);

        return new $config['oauthClass']([
            'clientId' => $config['clientIdKey'],
            'clientSecret' => $config['clientSecretKey'],
            'redirectUri' => $craftIdConfig['craftIdUrl'].'/apps/callback'
        ]);
    }

    /**
     * @param string $appTypeHandle
     *
     * @return array|null
     * @throws Exception if $appTypeHandle is invalid
     */
    public function getAppTypeConfig(string $appTypeHandle): array
    {
        if (!isset($this->appTypes[$appTypeHandle])) {
            throw new Exception('Invalid OAuth app type: '.$appTypeHandle);
        }

        return $this->appTypes[$appTypeHandle];
    }

    /**
     * @param string $providerClass
     * @param int    $userId
     *
     * @return string|null|false
     */
    public function getAuthTokenByUserId(string $providerClass, int $userId)
    {
        return (new Query())
            ->select(['accessToken'])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId, 'provider' => $providerClass])
            ->scalar();
    }

    /**
     * @param string $providerClass
     * @param int    $userId
     *
     * @return array|null
     */
    public function getOauthTokenByUserId(string $providerClass, int $userId)
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
     * @param array $tokenInfo
     *
     * @return AccessToken
     */
    public function createAccessToken(array $tokenInfo): AccessToken
    {
        $options = [
            'access_token' => $tokenInfo['accessToken'],
        ];

        if (isset($tokenInfo['expiresIn'])) {
            $options['expires_in'] = $tokenInfo['expiresIn'];
        }

        return new AccessToken($options);
    }
}
