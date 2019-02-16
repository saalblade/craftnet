<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\elements\Category;
use craft\elements\User;
use craft\helpers\Json;
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
     * Get Craft ID data.
     *
     * @return Response
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionIndex(): Response
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $currentUser = Craft::$app->getUser()->getIdentity();
        $photo = $currentUser->getPhoto();
        $photoUrl = $photo ? Craft::$app->getAssets()->getAssetUrl($photo, [
            'mode' => 'crop',
            'width' => 200,
            'height' => 200,
        ], true) : null;

        return $this->asJson([
            'billingAddress' => $this->getBillingAddress($currentUser),
            'currentUser' => [
                'id' => $currentUser->id,
                'email' => $currentUser->email,
                'username' => $currentUser->username,
                'firstName' => $currentUser->firstName,
                'lastName' => $currentUser->lastName,
                'developerName' => $currentUser->developerName,
                'developerUrl' => $currentUser->developerUrl,
                'location' => $currentUser->location,
                'enablePluginDeveloperFeatures' => $currentUser->isInGroup('developers') ? true : false,
                'enableShowcaseFeatures' => $currentUser->enableShowcaseFeatures == 1 ? true : false,
                'enablePartnerFeatures' => $currentUser->enablePartnerFeatures == 1 ? true : false,
                'groups' => $currentUser->getGroups(),
                'photoId' => $currentUser->getPhoto() ? $currentUser->getPhoto()->getId() : null,
                'photoUrl' => $photoUrl,
                'hasApiToken' => $currentUser->apiToken !== null,
            ],
            'card' => $this->getCard($currentUser),
            'cardToken' => $this->getCardToken($currentUser),
            'categories' => $this->getPluginCategories(),
            'countries' => Craft::$app->getApi()->getCountries(),
            'licenseExpiryDateOptions' => $this->getLicenseExpiryDateOptions($currentUser),
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param User $user
     *
     * @return array|null
     */
    private function getCard(User $user): ?array
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
    private function getCardToken(User $user): ?string
    {
        $paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

        if (\count($paymentSources) === 0) {
            return null;
        }

        $paymentSource = $paymentSources[0];

        return $paymentSource->token;
    }

    /**
     * @return array
     */
    private function getPluginCategories(): array
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

    /**
     * @param User $user
     * @return array
     * @throws \Exception
     */
    private function getLicenseExpiryDateOptions(User $user): array
    {
        $licenseExpiryDateOptions = [
            'cmsLicenses' => [],
            'pluginLicenses' => [],
        ];

        $cmsLicenses = Module::getInstance()->getCmsLicenseManager()->getLicensesArrayByOwner($user);
        $pluginLicenses = Module::getInstance()->getPluginLicenseManager()->getLicensesArrayByOwner($user);

        foreach ($cmsLicenses as $cmsLicense) {
            if (empty($cmsLicense['expiresOn'])) {
                continue;
            }

            $licenseExpiryDateOptions['cmsLicenses'][$cmsLicense['id']] = $this->getExpiryDateOptions($cmsLicense['expiresOn']);
        }

        foreach ($pluginLicenses as $pluginLicense) {
            if (empty($pluginLicense['expiresOn'])) {
                continue;
            }

            $licenseExpiryDateOptions['pluginLicenses'][$pluginLicense['id']] = $this->getExpiryDateOptions($pluginLicense['expiresOn']);
        }

        return $licenseExpiryDateOptions;
    }

    /**
     * @param User $user
     * @return array|null
     */
    private function getBillingAddress(User $user): ?array
    {
        $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);

        if (!$customer) {
            return null;
        }

        $primaryBillingAddress = $customer->getPrimaryBillingAddress();

        if (!$primaryBillingAddress) {
            return null;
        }

        $billingAddress = $primaryBillingAddress->toArray();

        $country = $primaryBillingAddress->getCountry();

        if ($country) {
            $billingAddress['country'] = $country->iso;
        }

        $state = $primaryBillingAddress->getState();

        if ($state) {
            $billingAddress['state'] = $state->abbreviation;
        }

        return $billingAddress;
    }
}
