<?php

namespace craftcom\id\controllers;

use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\helpers\Json;
use craftcom\api\controllers\BaseApiController;
use craftcom\plugins\Plugin;
use League\OAuth2\Client\Token\AccessToken;
use yii\web\Response;
use craft\db\Query;
use League\OAuth2\Client\Provider\Github;


/**
 * Class CraftIdController
 *
 * @package craftcom\id\controllers
 */
class CraftIdController extends BaseController
{
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
     * Handles /v1/craft-id requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        // Current user

        $currentUserId = Craft::$app->getRequest()->getParam('userId');
        $currentUser = Craft::$app->getUsers()->getUserById($currentUserId);

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

                $resourceOwner = $appProvider->getResourceOwner($accessToken);
                $account = $resourceOwner->toArray();

                $repositories = [];

                if($handle == 'github') {
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



        // Plugins

        $plugins = [];

        $pluginElements = Plugin::find()->developerId($currentUser->id)->status(null)->all();

        foreach ($pluginElements as $pluginElement) {
            $plugins[] = $this->pluginTransformer($pluginElement);
        }


        // Craft licenses

        $craftLicenses = [];

        $craftLicenseEntries = Entry::find()->section('licenses')->type('craftLicense')->authorId($currentUser->id)->all();

        foreach ($craftLicenseEntries as $craftLicenseEntry) {
            $craftLicense = $craftLicenseEntry->toArray();

            $plugin = null;

            if ($craftLicenseEntry->plugin) {
                $plugin = $craftLicenseEntry->plugin->toArray();
            }

            $craftLicense['plugin'] = $plugin;
            $craftLicense['author'] = $craftLicenseEntry->getAuthor()->toArray();
            $craftLicense['type'] = $craftLicenseEntry->getType()->handle;
            $craftLicenses[] = $craftLicense;
        }


        // Plugin licenses

        $pluginLicenses = [];

        $pluginLicenseEntries = Entry::find()->section('licenses')->type('pluginLicense')->authorId($currentUser->id)->all();

        foreach ($pluginLicenseEntries as $pluginLicenseEntry) {
            $pluginLicense = $pluginLicenseEntry->toArray();
            $plugin = $pluginLicenseEntry->plugin;
            $pluginLicense['plugin'] = $plugin->toArray();
            $craftLicense = $pluginLicenseEntry->craftLicense->one();

            if ($craftLicense) {
                $pluginLicense['craftLicense'] = $craftLicense->toArray();
            } else {
                $pluginLicense['craftLicense'] = null;
            }
            $pluginLicense['type'] = $pluginLicenseEntry->getType()->handle;
            $pluginLicense['author'] = $pluginLicenseEntry->getAuthor()->toArray();

            $pluginLicenses[] = $pluginLicense;
        }


        // Customers

        $customers = [];

        foreach ($pluginElements as $pluginElement) {
            $entries = Entry::find()->section('licenses')->relatedTo($pluginElement)->all();

            foreach ($entries as $entry) {

                $found = false;

                foreach ($customers as $c) {
                    if ($c['id'] == $entry->getAuthor()->id) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $customer = [
                        'id' => $entry->getAuthor()->id,
                        'email' => $entry->getAuthor()->email,
                        'username' => $entry->getAuthor()->username,
                        'fullName' => $entry->getAuthor()->fullName
                    ];

                    $customers[] = $customer;
                }
            }
        }


        // Categories

        $categories = [];
        $categoryElements = Category::find()->group('pluginCategories')->all();
        foreach ($categoryElements as $categoryElement) {
            $categories[] = [
                'id' => $categoryElement->id,
                'title' => $categoryElement->title,
            ];
        }


        // Data

        $data = [
            'currentUser' => [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'firstName' => $currentUser->firstName,
                'lastName' => $currentUser->lastName,
                'developerName' => $currentUser->developerName,
                'developerUrl' => $currentUser->developerUrl,
                'location' => $currentUser->location,
                'cardNumber' => $currentUser->cardNumber,
                'cardExpiry' => $currentUser->cardExpiry,
                'cardCvc' => $currentUser->cardCvc,
                'enablePluginDeveloperFeatures' => ($currentUser->isInGroup('developers') ? true : false),
                'enableShowcaseFeatures' => ($currentUser->enableShowcaseFeatures == 1 ? true : false),
                'businessName' => $currentUser->businessName,
                'businessVatId' => $currentUser->businessVatId,
                'businessAddressLine1' => $currentUser->businessAddressLine1,
                'businessAddressLine2' => $currentUser->businessAddressLine2,
                'businessCity' => $currentUser->businessCity,
                'businessState' => $currentUser->businessState,
                'businessZipCode' => $currentUser->businessZipCode,
                'businessCountry' => $currentUser->businessCountry,
                'vendor' => $currentUser->vendor,
                'groups' => $currentUser->getGroups(),
                'photoId' => ($currentUser->getPhoto() ? $currentUser->getPhoto()->getId() : null),
                // 'photoUrl' => ($currentUser->getPhoto() ? $currentUser->getPhoto()->getUrl() : null),
                'photoUrl' => $currentUser->getThumbUrl(200),
            ],
            'apps' => $apps,
            'plugins' => $plugins,
            'craftLicenses' => $craftLicenses,
            'pluginLicenses' => $pluginLicenses,
            'customers' => $customers,
            'payouts' => $this->_getPayouts(),
            'payoutsScheduled' => $this->_getScheduledPayouts(),
            'payments' => $this->_getPayments(),
            'categories' => $categories,
        ];

        return $this->asJson($data);
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

    private function _getPayouts()
    {
        return [
            [
                'id' => 1,
                'amount' => 99.00,
                'date' => '1 year ago',
                'bank' => [
                    'name' => 'BNP Parisbas',
                    'accountNumber' => '2345678923456783456',
                ]
            ],
            [
                'id' => 2,
                'amount' => 99.00,
                'date' => '1 year ago',
                'bank' => [
                    'name' => 'BNP Parisbas',
                    'accountNumber' => '2345678923456783456',
                ]
            ],
            [
                'id' => 3,
                'amount' => 298.00,
                'date' => '1 year ago',
                'bank' => [
                    'name' => 'BNP Parisbas',
                    'accountNumber' => '2345678923456783456',
                ]
            ],
        ];
    }

    private function _getScheduledPayouts()
    {
        return [
            [
                'id' => 8,
                'amount' => 116.00,
                'date' => 'Tomorrow',
            ],
        ];
    }

    private function _getPayments()
    {
        return [
            [
                'items' => [['id' => 6, 'name' => 'Analytics']],
                'amount' => 99.00,
                'customer' => [
                    'id' => 1,
                    'name' => 'Benjamin David',
                    'email' => 'ben@pixelandtonic.com',
                ],
                'date' => '3 days ago',
            ],
            [
                'items' => [['id' => 6, 'name' => 'Analytics']],
                'amount' => 99.00,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
                'date' => '1 year ago',
            ],
            [
                'items' => [['id' => 7, 'name' => 'Videos']],
                'amount' => 99.00,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
                'date' => '1 year ago',
            ],
            [
                'items' => [['id' => 6, 'name' => 'Analytics'], ['id' => 7, 'name' => 'Videos']],
                'amount' => 298.00,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
                'date' => '1 year ago',
            ],
        ];
    }
}
