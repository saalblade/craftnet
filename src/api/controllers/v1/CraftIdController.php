<?php

namespace craftcom\api\controllers\v1;

use Craft;
use craft\elements\Entry;
use craftcom\api\controllers\BaseApiController;
use yii\web\Response;

/**
 * Class CraftIdController
 *
 * @package craftcom\api\controllers\v1
 */
class CraftIdController extends BaseApiController
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


        // Plugins

        $plugins = [];

        $pluginEntries = Entry::find()->section('plugins')->authorId($currentUser->id)->all();

        foreach($pluginEntries as $pluginEntry) {
            $plugins[] = $this->pluginTransformer($pluginEntry);
        }


        // Craft licenses

        $craftLicenses = [];

        $craftLicenseEntries = Entry::find()->section('licenses')->type('craftLicense')->authorId($currentUser->id)->all();

        foreach($craftLicenseEntries as $craftLicenseEntry) {
            $craftLicense = $craftLicenseEntry->toArray();
            $craftLicense['plugin'] = $craftLicenseEntry->plugin->one()->toArray();
            $craftLicense['author'] = $craftLicenseEntry->getAuthor()->toArray();
            $craftLicense['type'] = $craftLicenseEntry->getType()->handle;
            $craftLicenses[] = $craftLicense;
        }


        // Plugin licenses

        $pluginLicenses = [];

        $pluginLicenseEntries = Entry::find()->section('licenses')->type('pluginLicense')->authorId($currentUser->id)->all();

        foreach($pluginLicenseEntries as $pluginLicenseEntry) {
            $pluginLicense = $pluginLicenseEntry->toArray();
            $pluginLicense['plugin'] = $pluginLicenseEntry->plugin->one()->toArray();
            $craftLicense = $pluginLicenseEntry->craftLicense->one();

            if($craftLicense) {
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

        foreach($pluginEntries as $pluginEntry) {
            $entries = Entry::find()->section('licenses')->relatedTo($pluginEntry)->all();

            foreach($entries as $entry) {

                $found = false;

                foreach($customers as $c) {
                    if($c['id'] == $entry->getAuthor()->id) {
                        $found = true;
                    }
                }

                if(!$found) {
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


        // Data

        $data = [
            'currentUser' => [
                'id' => $currentUser->id,
                'firstName' => $currentUser->firstName,
                'lastName' => $currentUser->lastName,
                'developerName' => $currentUser->developerName,
                'developerUrl' => $currentUser->developerUrl,
                'location' => $currentUser->location,
                'cardNumber' => $currentUser->cardNumber,
                'cardExpiry' => $currentUser->cardExpiry,
                'cardCvc' => $currentUser->cardCvc,
                'enablePluginDeveloperFeatures' => ($currentUser->enablePluginDeveloperFeatures == 1 ? true : false),
                'enableShowcaseFeatures' => ($currentUser->enableShowcaseFeatures == 1 ? true : false),
                'businessName' => $currentUser->businessName,
                'businessVatId' => $currentUser->businessVatId,
                'businessAddressLine1' => $currentUser->businessAddressLine1,
                'businessAddressLine2' => $currentUser->businessAddressLine2,
                'businessCity' => $currentUser->businessCity,
                'businessState' => $currentUser->businessState,
                'businessZipCode' => $currentUser->businessZipCode,
                'businessCountry' => $currentUser->businessCountry,
            ],
            'plugins' => $plugins,
            'craftLicenses' => $craftLicenses,
            'pluginLicenses' => $pluginLicenses,
            'customers' => $customers,
        ];

        return $this->asJson($data);
    }
}
