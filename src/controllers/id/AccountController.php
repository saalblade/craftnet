<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\models\Address;
use craft\commerce\Plugin as Commerce;
use craft\elements\Asset;
use craft\elements\User;
use craft\errors\UploadFailedException;
use craft\helpers\Assets;
use craft\helpers\FileHelper;
use craft\helpers\Json;
use craft\web\Controller;
use craft\web\UploadedFile;
use Throwable;
use yii\base\UserException;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Class AccountController
 */
class AccountController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * @return Response
     */
    public function actionGetAccount()
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $photo = $currentUser->getPhoto();
        $photoUrl = $photo ? Craft::$app->getAssets()->getAssetUrl($photo, [
            'mode' => 'crop',
            'width' => 200,
            'height' => 200,
        ], true) : null;

        return $this->asJson([
            'billingAddress' => $this->getBillingAddress($currentUser),
            'card' => $this->getCard($currentUser),
            'cardToken' => $this->getCardToken($currentUser),
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
        ]);
    }

    /**
     * Upload a user photo.
     *
     * @return null|Response
     * @throws BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionUploadUserPhoto()
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        if (($file = UploadedFile::getInstanceByName('photo')) === null) {
            return null;
        }

        try {
            if ($file->getHasError()) {
                throw new UploadFailedException($file->error);
            }

            $user = Craft::$app->getUser()->getIdentity();

            // Move to our own temp location
            $fileLocation = Assets::tempFilePath($file->getExtension());
            move_uploaded_file($file->tempName, $fileLocation);
            Craft::$app->getUsers()->saveUserPhoto($fileLocation, $user, $file->name);

            $photo = $user->getPhoto();
            $photoUrl = $photo ? Craft::$app->getAssets()->getAssetUrl($photo, [
                'mode' => 'crop',
                'width' => 200,
                'height' => 200,
            ], true) : null;

            return $this->asJson([
                'photoId' => $user->photoId,
                'photoUrl' => $photoUrl,
            ]);
        } catch (\Throwable $exception) {
            /** @noinspection UnSafeIsSetOverArrayInspection - FP */
            if (isset($fileLocation) && file_exists($fileLocation)) {
                FileHelper::unlink($fileLocation);
            }

            Craft::error('There was an error uploading the photo: ' . $exception->getMessage(), __METHOD__);

            return $this->asErrorJson(Craft::t('app', 'There was an error uploading your photo: {error}', [
                'error' => $exception->getMessage()
            ]));
        }
    }

    /**
     * Delete all the photos for current user.
     *
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDeleteUserPhoto(): Response
    {
        $this->requireAcceptsJson();
        $this->requireLogin();

        $user = Craft::$app->getUser()->getIdentity();

        if ($user->photoId) {
            Craft::$app->getElements()->deleteElementById($user->photoId, Asset::class);
        }

        $user->photoId = null;

        Craft::$app->getElements()->saveElement($user, false);

        return $this->asJson([
            'photoId' => $user->photoId,
            'photoUrl' => $user->getThumbUrl(200),
        ]);
    }

    /**
     * Generate API token.
     *
     * @return Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionGenerateApiToken(): Response
    {
        $this->requirePostRequest();
        $this->requireLogin();

        $user = Craft::$app->getUser()->getIdentity();

        if (!$user->isInGroup('developers')) {
            throw new ForbiddenHttpException('User is not permitted to perform this action');
        }

        try {
            $apiToken = $user->generateApiToken();

            return $this->asJson(['apiToken' => $apiToken]);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }

    /**
     * Save billing info.
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionSaveBillingInfo(): Response
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $address = new Address();
        $address->id = Craft::$app->getRequest()->getBodyParam('id');
        $address->firstName = Craft::$app->getRequest()->getBodyParam('firstName');
        $address->lastName = Craft::$app->getRequest()->getBodyParam('lastName');
        $address->businessName = Craft::$app->getRequest()->getBodyParam('businessName');
        $address->businessTaxId = Craft::$app->getRequest()->getBodyParam('businessTaxId');
        $address->address1 = Craft::$app->getRequest()->getBodyParam('address1');
        $address->address2 = Craft::$app->getRequest()->getBodyParam('address2');
        $address->city = Craft::$app->getRequest()->getBodyParam('city');
        $address->zipCode = Craft::$app->getRequest()->getBodyParam('zipCode');

        $countryIso = Craft::$app->getRequest()->getBodyParam('country');
        $stateAbbr = Craft::$app->getRequest()->getBodyParam('state');

        if ($countryIso) {
            $country = Commerce::getInstance()->getCountries()->getCountryByIso($countryIso);

            if ($country) {
                $address->countryId = $country->id;

                if (!empty($stateAbbr)) {
                    $state = Commerce::getInstance()->getStates()->getStateByAbbreviation($country->id, $stateAbbr);
                    $address->stateId = $state ? $state->id : null;
                }
            }
        }

        try {
            // save the address
            $customerService = Commerce::getInstance()->getCustomers();
            if (!$customerService->saveAddress($address)) {
                $errors = implode(', ', $address->getErrorSummary(false));
                throw new UserException($errors ?: 'An error occurred saving the billing address.');
            }

            // set this as the user's primary billing address
            $customer = $customerService->getCustomer();
            $customer->primaryBillingAddressId = $address->id;
            if (!$customerService->saveCustomer($customer)) {
                $errors = implode(', ', $customer->getErrorSummary(false));
                throw new UserException($errors ?: 'An error occurred saving the billing address.');
            }

            // return the address info
            $addressArray = $address->toArray();
            if ($countryIso) {
                $addressArray['country'] = $countryIso;
            }
            if ($stateAbbr) {
                $addressArray['state'] = $stateAbbr;
            }
            return $this->asJson([
                'success' => true,
                'address' => $addressArray,
            ]);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
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

        $billingAddress['country'] = '';

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
