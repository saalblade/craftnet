<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\elements\Category;
use craft\elements\User;
use craftnet\Module;
use yii\web\Response;

/**
 * Class CraftIdController
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
        $this->requireLogin();


        // Current user

        $currentUser = Craft::$app->getUser()->getIdentity();


        // Craft ID config

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $enableCommercialFeatures = $craftIdConfig['enableCommercialFeatures'];


        // Billing address

        $billingAddress = null;

        $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($currentUser->id);

        if ($customer) {
            $customerAddresses = $customer->getAddresses();

            if (count($customerAddresses)) {
                $billingAddress = end($customerAddresses);
                $billingAddressArray = $billingAddress->toArray();

                $country = $billingAddress->getCountry();

                if($country) {
                    $billingAddressArray['country'] = $country->iso;
                }

                $state = $billingAddress->getState();

                if($state) {
                    $billingAddressArray['state'] = $state->abbreviation;
                }
            }
        }


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
                'hasApiToken' => ($currentUser->apiToken !== null),
            ],
            'billingAddress' => $billingAddressArray,
            'countries' => Craft::$app->getApi()->getCountries(),
            'apps' => Module::getInstance()->getOauth()->getApps(),
            'plugins' => $this->_plugins($currentUser),
            'cmsLicenses' => $this->_cmsLicenses($currentUser),
            'pluginLicenses' => $this->_pluginLicenses($currentUser),
            'customers' => $this->_customers($currentUser),
            'sales' => $this->_sales(),
            'upcomingInvoice' => $this->_upcomingInvoice(),
            'categories' => $this->_pluginCategories(),
            'enableCommercialFeatures' => $enableCommercialFeatures
        ];

        return $this->asJson($data);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param User $user
     *
     * @return array
     */
    private function _plugins(User $user): array
    {
        $ret = [];

        foreach ($user->getPlugins() as $plugin) {
            $ret[] = $this->pluginTransformer($plugin);
        }

        return $ret;
    }

    /**
     * @param User $user
     *
     * @return array CMS licenses.
     */
    private function _cmsLicenses(User $user): array
    {
        return Module::getInstance()->getCmsLicenseManager()->getLicensesArrayByOwner($user);
    }

    /**
     * @param User $user
     *
     * @return array Plugin licenses.
     */
    private function _pluginLicenses(User $user): array
    {
        return Module::getInstance()->getPluginLicenseManager()->getLicensesArrayByOwner($user);
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function _customers(User $user): array
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
            'datePaid' => date('Y-m-d'),
            'paymentMethod' => [
                'type' => 'visa',
                'last4' => '2424',
            ],
            'items' => [
                ['id' => 6, 'name' => 'Analytics', 'amount' => 29, 'type' => 'renewal'],
                ['id' => 8, 'name' => 'Social', 'amount' => 99, 'type' => 'license']
            ],
            'totalPrice' => 128,
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
