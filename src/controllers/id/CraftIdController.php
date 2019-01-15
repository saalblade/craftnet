<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\elements\Category;
use craft\elements\User;
use craft\helpers\Json;
use craftnet\developers\UserBehavior;
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


        // Billing address

        $billingAddressArray = null;

        $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($currentUser->id);

        if ($customer && $billingAddress = $customer->getPrimaryBillingAddress()) {
            $billingAddressArray = $billingAddress->toArray();

            $country = $billingAddress->getCountry();

            if ($country) {
                $billingAddressArray['country'] = $country->iso;
            }

            $state = $billingAddress->getState();

            if ($state) {
                $billingAddressArray['state'] = $state->abbreviation;
            }
        }


        // Data

        $photo = $currentUser->getPhoto();
        $photoUrl = $photo ? Craft::$app->getAssets()->getAssetUrl($photo, [
            'mode' => 'crop',
            'width' => 200,
            'height' => 200,
        ], true) : null;

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
                'enablePartnerFeatures' => ($currentUser->enablePartnerFeatures == 1 ? true : false),
                'groups' => $currentUser->getGroups(),
                'photoId' => $currentUser->getPhoto() ? $currentUser->getPhoto()->getId() : null,
                'photoUrl' => $photoUrl,
                'hasApiToken' => ($currentUser->apiToken !== null),
            ],
            'card' => $this->_card($currentUser),
            'cardToken' => $this->_cardToken($currentUser),
            'billingAddress' => $billingAddressArray,
            'countries' => Craft::$app->getApi()->getCountries(),
            'apps' => Module::getInstance()->getOauth()->getApps(),
            'plugins' => $this->_plugins($currentUser),
            'cmsLicenses' => $this->_cmsLicenses($currentUser),
            'pluginLicenses' => $this->_pluginLicenses($currentUser),
            'licenseExpiryDateOptions' => [
                'cmsLicenses' => [],
                'pluginLicenses' => [],
            ],
            'sales' => $this->_sales($currentUser),
            'upcomingInvoice' => $this->_upcomingInvoice(),
            'categories' => $this->_pluginCategories(),
        ];


        // Build expiry date options

        foreach ($data['cmsLicenses'] as $cmsLicense) {
            if (empty($cmsLicense['expiresOn'])) {
                continue;
            }

            $data['licenseExpiryDateOptions']['cmsLicenses'][$cmsLicense['id']] = $this->getExpiryDateOptions($cmsLicense['expiresOn']);
        }

        foreach ($data['pluginLicenses'] as $pluginLicense) {
            if (empty($pluginLicense['expiresOn'])) {
                continue;
            }

            $data['licenseExpiryDateOptions']['pluginLicenses'][$pluginLicense['id']] = $this->getExpiryDateOptions($pluginLicense['expiresOn']);
        }

        return $this->asJson($data);
    }

    public function actionPluginStoreData()
    {
        $pluginStoreData = Craft::$app->getApi()->getPluginStoreData();

        return $this->asJson($pluginStoreData);
    }

    // Private Methods
    // =========================================================================

    /**
     * Get expiry date options.
     *
     * @param \DateTime $expiryDate
     * @return array
     * @throws \Exception
     */
    private function getExpiryDateOptions(\DateTime $expiryDate)
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $dates = [];

        for ($i = 1; $i <= 5; $i++) {
            if ($expiryDate < $now) {
                $date =  (new \DateTime('now', new \DateTimeZone('UTC')))
                    ->modify("+{$i} years");
                $dates[] = ["{$i}y", $date->format('Y-m-d')];
            } else {
                $date = clone $expiryDate;
                $date =  $date->modify("+{$i} years");
                $dates[] = ["{$date->format('Y-m-d')}", $date->format('Y-m-d')];
            }
        }

        return $dates;
    }

    /**
     * @param User $user
     *
     * @return array|null
     */
    private function _card(User $user)
    {
        $paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

        if (\count($paymentSources) === 0) {
            return null;
        }

        $paymentSource = $paymentSources[0];
        $response = Json::decode($paymentSource->response);

        if (isset($response['object']) && $response['object'] === 'card') {
            return $response;
        } elseif (isset($response['object']) && $response['object'] === 'source') {
            return $response['card'];
        }

        return null;
    }

    /**
     * @param User $user
     * @return null|string
     */
    private function _cardToken(User $user)
    {
        $paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

        if (\count($paymentSources) === 0) {
            return null;
        }

        $paymentSource = $paymentSources[0];

        return $paymentSource->token;
    }

    /**
     * @param User $user
     *
     * @return array
     */
    private function _plugins(User $user): array
    {
        /** @var UserBehavior|User $user */
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
