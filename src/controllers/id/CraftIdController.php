<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\models\Address;
use craft\commerce\Plugin as Commerce;
use craft\elements\Category;
use craft\elements\User;
use craftnet\cms\CmsEdition;
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requireLogin();
        $this->requirePostRequest();


        // Current user

        $currentUser = Craft::$app->getUser()->getIdentity();


        // Craft ID config

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');
        $enableRenewalFeatures = $craftIdConfig['enableRenewalFeatures'];


        // Billing address

        $billingAddressArray = null;

        $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($currentUser->id);

        if ($customer && $billingAddress = $customer->getPrimaryBillingAddress()) {
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
                'groups' => $currentUser->getGroups(),
                'photoId' => ($currentUser->getPhoto() ? $currentUser->getPhoto()->getId() : null),
                'photoUrl' => $currentUser->getThumbUrl(200),
                'hasApiToken' => ($currentUser->apiToken !== null),
            ],
            'billingAddress' => $billingAddressArray,
            'countries' => Craft::$app->getApi()->getCountries(),
            'apps' => Module::getInstance()->getOauth()->getApps(),
            'plugins' => $this->_plugins($currentUser),
            'cmsLicenses' => $this->_cmsLicenses($currentUser),
            'pluginLicenses' => $this->_pluginLicenses($currentUser),
            'renewLicenses' => $this->_renewLicenses($currentUser),
            'sales' => $this->_sales($currentUser),
            'upcomingInvoice' => $this->_upcomingInvoice(),
            'categories' => $this->_pluginCategories(),
            'enableRenewalFeatures' => $enableRenewalFeatures
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
     * @return array licenses that need to be renewed.
     */
    private function _renewLicenses(User $user): array
    {
        $renewLicenses = [];

        $cmsLicenses = Module::getInstance()->getCmsLicenseManager()->getRenewLicensesByOwner($user->id);

        foreach ($cmsLicenses as $cmsLicense) {
            $edition = CmsEdition::findOne($cmsLicense->editionId);

            $renewLicenses[] = [
                'id' => $cmsLicense->id,
                'type' => 'cmsLicense',
                'shortKey' => substr($cmsLicense->key, 0, 10),
                'item' => 'Craft '.$edition->name,
                'domain' => $cmsLicense->domain,
                'renewalDate' => $cmsLicense->dateCreated,
                'renewalPrice' => $edition->renewalPrice,
            ];
        }

        $pluginLicenses = Module::getInstance()->getPluginLicenseManager()->getLicensesByOwner($user->id);

        foreach ($pluginLicenses as $pluginLicense) {
            $plugin = $pluginLicense->getPlugin();

            $renewLicenses[] = [
                'id' => $pluginLicense->id,
                'type' => 'pluginLicense',
                'shortKey' => substr($pluginLicense->key, 0, 10),
                'item' => $plugin->name,
                'domain' => null,
                'renewalDate' => $pluginLicense->dateCreated,
                'renewalPrice' => $plugin->renewalPrice,
            ];
        }

        return $renewLicenses;
    }

    /**
     * @return array
     */
    private function _sales(User $user): array
    {
        return Module::getInstance()->getPluginLicenseManager()->getSalesArrayByPluginOwner($user);
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
