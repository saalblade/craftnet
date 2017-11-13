<?php

namespace craftcom\id\controllers;

use Craft;
use craft\web\Controller;
use craft\helpers\Json;
use craftcom\plugins\Plugin;
use GuzzleHttp\Client;
use League\OAuth2\Client\Token\AccessToken;
use craft\db\Query;
use League\OAuth2\Client\Provider\Github;

/**
 * Class BaseController
 *
 * @property array $apps
 */
abstract class BaseController extends Controller
{
    // Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @var array
     */
    private $_appTypes = [
        'github' => [
            'class' => 'Github',
            'oauthClass' => 'League\OAuth2\Client\Provider\Github',
            'clientIdKey' => 'GITHUB_APP_CLIENT_ID',
            'clientSecretKey' => 'GITHUB_APP_CLIENT_SECRET',
            'scope' => ['user:email', 'write:repo_hook', 'repo'],
        ],
        'bitbucket' => [
            'class' => 'Bitbucket',
            'oauthClass' => 'Stevenmaguire\OAuth2\Client\Provider\Bitbucket',
            'clientIdKey' => 'BITBUCKET_APP_CLIENT_ID',
            'clientSecretKey' => 'BITBUCKET_APP_CLIENT_SECRET',
            'scope' => 'account',
        ],
    ];

    // Protected Methods
    // =========================================================================

    /**
     * @return array
     */
    protected function getApps(): array
    {
        $currentUser = Craft::$app->getUser()->getIdentity();

        $apps = [];

        foreach($this->_appTypes as $handle => $config) {
            $oauthProvider = $this->getAppTypeOauthProvider($handle);
            $token = $this->_getOauthTokenByUserId($config['class'], $currentUser->id);

            if ($token) {
                $options = [
                    'access_token' => $token['accessToken'],
                ];

                if(isset($token['expiresIn'])) {
                    $options['expires_in'] = $token['expiresIn'];
                }

                $accessToken = new AccessToken($options);

                $resourceOwner = $oauthProvider->getResourceOwner($accessToken);
                $account = $resourceOwner->toArray();

                $repositories = [];

                if($handle === 'github') {
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
     * @param Plugin $plugin
     *
     * @return array
     */
    protected function pluginTransformer(Plugin $plugin): array
    {
        // Developer name
        $developerName = $plugin->getDeveloper()->developerName;

        if (empty($developerName)) {
            $developerName = $plugin->getDeveloper()->getFullName();
        }

        // Icon url
        $iconUrl = null;
        $icon = $plugin->icon;

        if ($icon) {
            $iconUrl = $icon->getUrl();
        }

        // Screenshots
        $screenshotUrls = [];
        $screenshotIds = [];

        foreach ($plugin->screenshots as $screenshot) {
            $screenshotUrls[] = $screenshot->getUrl();
            $screenshotIds[] = $screenshot->getId();
        }

        // Categories
        $categoryIds = [];

        foreach ($plugin->categories as $category) {
            $categoryIds[] = $category->id;
        }

        // Package
        try {
            $client = new Client();
            $response = $client->get('https://packagist.org/packages/'.$plugin->getDeveloper()->vendor.'/'.$plugin->slug.'.json');
            $data = Json::decode($response->getBody()->getContents());
            $package = $data['package'];
        } catch (\Exception $e) {
            $package = null;
        }

        return [
            'id' => $plugin->id,
            'status' => $plugin->status,
            'iconId' => $plugin->iconId,
            'iconUrl' => $iconUrl,
            'packageName' => $plugin->packageName,
            'handle' => $plugin->handle,
            'name' => $plugin->name,
            'shortDescription' => $plugin->shortDescription,
            'longDescription' => $plugin->longDescription,
            'documentationUrl' => $plugin->documentationUrl,
            'changelogUrl' => $plugin->changelogUrl,
            'repository' => $plugin->repository,
            'license' => $plugin->license,
            'price' => $plugin->price,
            'renewalPrice' => $plugin->renewalPrice,

            // 'iconUrl' => $iconUrl,
            'developerId' => $plugin->getDeveloper()->id,
            'developerName' => $developerName,
            'developerUrl' => $plugin->getDeveloper()->developerUrl,
            'developerVendor' => $plugin->getDeveloper()->vendor,

            'screenshotUrls' => $screenshotUrls,
            'screenshotIds' => $screenshotIds,
            'categoryIds' => $categoryIds,
            'package' => $package,
        ];
    }

    /**
     * @return Github
     */
    protected function getAppTypeOauthProvider($appTypeHandle)
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $config = $this->getAppTypeConfig($appTypeHandle);

        if($config) {
            return new $config['oauthClass']([
                'clientId' => isset($_SERVER[$config['clientIdKey']]) ? $_SERVER[$config['clientIdKey']] : getenv($config['clientIdKey']),
                'clientSecret' => isset($_SERVER[$config['clientSecretKey']]) ? $_SERVER[$config['clientSecretKey']] : getenv($config['clientSecretKey']),
                'redirectUri' => $craftIdConfig['craftIdUrl'].'/apps/callback'
            ]);
        }
    }

    /**
     * @param $appTypeHandle
     *
     * @return mixed
     */
    protected function getAppTypeConfig($appTypeHandle)
    {
        if(isset($this->_appTypes[$appTypeHandle])) {
            return $this->_appTypes[$appTypeHandle];
        }
    }

    /**
     * @param int $userId
     *
     * @return false|null|string
     */
    protected function getAuthTokenByUserId($providerClass, int $userId)
    {
        return (new Query())
            ->select(['accessToken'])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId, 'provider' => $providerClass])
            ->scalar();
    }

    // Private Methods
    // =========================================================================

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
}
