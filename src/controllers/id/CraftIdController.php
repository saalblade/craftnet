<?php

namespace craftcom\controllers\id;

use Craft;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\User;
use craft\helpers\Json;
use craftcom\behaviors\Developer;
use craftcom\controllers\api\BaseApiController;
use craftcom\Module;
use craftcom\plugins\Plugin;
use League\OAuth2\Client\Token\AccessToken;
use yii\web\Response;
use craft\db\Query;
use League\OAuth2\Client\Provider\Github;


/**
 * Class CraftIdController
 *
 * @package craftcom\controllers\id
 */
class CraftIdController extends BaseController
{
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

        // Craft ID config
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $enableCommercialFeatures = $craftIdConfig['enableCommercialFeatures'];

        // Data
        $data = [
            'currentUser' => [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'username' => $currentUser->username,
                'firstName' => $currentUser->firstName,
                'lastName' => $currentUser->lastName,
                'developerName' => $currentUser->developerName,
                'developerUrl' => $currentUser->developerUrl,
                'location' => $currentUser->location,
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
                'groups' => $currentUser->getGroups(),
                'photoId' => ($currentUser->getPhoto() ? $currentUser->getPhoto()->getId() : null),
                // 'photoUrl' => ($currentUser->getPhoto() ? $currentUser->getPhoto()->getUrl() : null),
                'photoUrl' => $currentUser->getThumbUrl(200),
            ],
            'apps' => Module::getInstance()->getOauth()->getApps(),
            'plugins' => $this->_plugins($currentUser),
            'craftLicenses' => $this->_craftLicenses($currentUser),
            'pluginLicenses' => $this->_pluginLicenses($currentUser),
            'customers' => $this->_customers($currentUser),
            'sales' => $this->_sales(),
            'upcomingInvoice' => $this->_upcomingInvoice(),
            'invoices' => $this->_invoices(),
            'categories' => $this->_pluginCategories(),
            'enableCommercialFeatures' => $enableCommercialFeatures
        ];

        return $this->asJson($data);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param User|Developer $currentUser
     *
     * @return array
     */
    private function _plugins(User $currentUser): array
    {
        $ret = [];

        foreach ($currentUser->getPlugins() as $plugin) {
            $ret[] = $this->pluginTransformer($plugin);
        }

        return $ret;
    }

    /**
     * @param User|Developer $currentUser
     *
     * @return array
     */
    private function _craftLicenses(User $currentUser): array
    {
        $ret = [];
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
            $ret[] = $craftLicense;
        }

        return $ret;
    }

    /**
     * @param User|Developer $currentUser
     *
     * @return array
     */
    private function _pluginLicenses(User $currentUser): array
    {
        $ret = [];
        $pluginLicenseEntries = Entry::find()
            ->section('licenses')
            ->type('pluginLicense')
            ->authorId($currentUser->id)
            ->all();

        foreach ($pluginLicenseEntries as $pluginLicenseEntry) {
            $pluginLicense = $pluginLicenseEntry->toArray();
            $plugin = $pluginLicenseEntry->plugin->one();
            $pluginLicense['plugin'] = $plugin->toArray();
            $craftLicense = $pluginLicenseEntry->craftLicense->one();

            if ($craftLicense) {
                $pluginLicense['craftLicense'] = $craftLicense->toArray();
            } else {
                $pluginLicense['craftLicense'] = null;
            }
            $pluginLicense['type'] = $pluginLicenseEntry->getType()->handle;
            $pluginLicense['author'] = $pluginLicenseEntry->getAuthor()->toArray();

            $ret[] = $pluginLicense;
        }

        return $ret;
    }

    /**
     * @param User|Developer $currentUser
     *
     * @return array
     */
    private function _customers(User $currentUser): array
    {
        return [
            [
                'id' => 1,
                'email' => 'ben@pixelandtonic.com',
                'username' => 'benjamin',
                'fullName' => 'Benjamin David',
            ],
            [
                'id' => 2,
                'email' => 'brandon@pixelandtonic.com',
                'username' => 'brandon',
                'fullName' => 'Brandon Kelly',
            ]
        ];
    }

    /**
     * @return array
     */
    private function _sales(): array
    {
        return [
            [
                'id' => 3,
                'plugin' => ['id' => 6, 'name' => 'Analytics'],
                'type' => 'license',
                'grossAmount' => 99.00,
                'netAmount' => 79.20,
                'customer' => [
                    'id' => 2,
                    'name' => 'Brandon Kelly',
                    'email' => 'brandon@pixelandtonic.com',
                ],
                'date' => date('Y-m-d'),
            ],
            [
                'id' => 2,
                'plugin' => ['id' => 6, 'name' => 'Analytics'],
                'type' => 'renewal',
                'grossAmount' => 29.00,
                'netAmount' => 23.20,
                'customer' => [
                    'id' => 1,
                    'name' => 'Benjamin David',
                    'email' => 'ben@pixelandtonic.com',
                ],
                'date' => date('Y-m-d'),
            ],
            [
                'id' => 1,
                'plugin' => ['id' => 6, 'name' => 'Analytics'],
                'type' => 'license',
                'grossAmount' => 99.00,
                'netAmount' => 79.20,
                'customer' => [
                    'id' => 1,
                    'name' => 'Benjamin David',
                    'email' => 'ben@pixelandtonic.com',
                ],
                'date' => date('Y-m-d', strtotime('-1 year')),
            ],
        ];
    }

    /**
     * @return array
     */
    private function _upcomingInvoice(): array
    {
        return [
            'id' => 5,
            'date' => date('Y-m-d'),
            'paymentMethod' => [
                'type' => 'visa',
                'last4' => '2424',
            ],
            'items' => [
                ['id' => 6, 'name' => 'Analytics', 'amount' => 29, 'type' => 'renewal'],
                ['id' => 8, 'name' => 'Social', 'amount' => 99, 'type' => 'license']
            ],
            'total' => 128,
            'customer' => [
                'id' => 1,
                'name' => 'Benjamin David',
                'email' => 'ben@pixelandtonic.com',
            ],
        ];
    }

    /**
     * @return array
     */
    private function _invoices(): array
    {
        return [
            [
                'id' => 1,
                'date' => date('Y-m-d'),
                'paymentMethod' => [
                    'type' => 'visa',
                    'last4' => '2424',
                ],
                'items' => [
                    ['id' => 6, 'name' => 'Analytics', 'amount' => 29, 'type' => 'renewal'],
                    ['id' => 8, 'name' => 'Social', 'amount' => 99, 'type' => 'license']
                ],
                'total' => 128,
                'customer' => [
                    'id' => 1,
                    'name' => 'Benjamin David',
                    'email' => 'ben@pixelandtonic.com',
                ],
            ],
            [
                'id' => 2,
                'date' => date('Y-m-d'),
                'paymentMethod' => [
                    'type' => 'visa',
                    'last4' => '2424',
                ],
                'items' => [
                    ['id' => 6, 'name' => 'Analytics', 'amount' => 29, 'type' => 'renewal']
                ],
                'total' => 29,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
            ],
            [
                'id' => 3,
                'date' => date('Y-m-d'),
                'paymentMethod' => [
                    'type' => 'visa',
                    'last4' => '2424',
                ],
                'items' => [
                    ['id' => 7, 'name' => 'Videos', 'amount' => 29, 'type' => 'renewal']
                ],
                'total' => 29,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
            ],
            [
                'id' => 4,
                'date' => date('Y-m-d'),
                'paymentMethod' => [
                    'type' => 'visa',
                    'last4' => '2424',
                ],
                'items' => [
                    ['id' => 6, 'name' => 'Analytics', 'amount' => 99, 'type' => 'license'],
                    ['id' => 7, 'name' => 'Videos', 'amount' => 99, 'type' => 'license']
                ],
                'total' => 198,
                'customer' => [
                    'id' => 15,
                    'name' => 'Andrew Welsh',
                    'email' => 'andrew@nystudio107.com',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    private function _pluginCategories(): array
    {
        $ret = [];
        $categories = Category::find()
            ->group('pluginCategories')
            ->all();

        foreach ($categories as $category) {
            $ret[] = [
                'id' => $category->id,
                'title' => $category->title,
            ];
        }

        return $ret;
    }
}
