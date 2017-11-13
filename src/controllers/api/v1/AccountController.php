<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\elements\User;
use craft\helpers\Db;
use craftcom\controllers\api\BaseApiController;
use craftcom\oauthserver\Module as OauthServer;
use craftcom\records\StripeCustomer as StripeCustomerRecord;
use Stripe\Customer;
use Stripe\Stripe;
use yii\web\Response;

/**
 * Class AccountController
 *
 * @package craftcom\controllers\api\v1
 */
class AccountController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/account requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        /*try {*/
        // Retrieve access token
        $accessToken = OauthServer::getInstance()->getAccessTokens()->getAccessTokenFromRequest();

        if ($accessToken) {
            // Check that this access token is associated with a user
            if ($accessToken->userId) {
                // Check that the user has sufficient permissions to access the resource
                $scopes = $accessToken->scopes;
                $requiredScopes = ['purchasePlugins', 'existingPlugins', 'transferPluginLicense', 'deassociatePluginLicense'];

                $hasSufficientPermissions = true;

                foreach ($requiredScopes as $requiredScope) {
                    if (!in_array($requiredScope, $scopes)) {
                        $hasSufficientPermissions = false;
                    }
                }

                if ($hasSufficientPermissions) {
                    // User has sufficient permissions to access the resource

                    $user = User::find()->id($accessToken->userId)->one();

                    if ($user) {

                        $purchasedPlugins = [];

                        foreach ($user->purchasedPlugins->all() as $purchasedPlugin) {
                            $purchasedPlugins[] = [
                                'name' => $purchasedPlugin->title,
                                'developerName' => $purchasedPlugin->getAuthor()->developerName,
                                'developerUrl' => $purchasedPlugin->getAuthor()->developerUrl,
                            ];
                        }

                        $card = null;
                        $stripeCustomer = null;

                        $stripeCustomerRecord = StripeCustomerRecord::find()
                            ->where(Db::parseParam('userId', $accessToken->userId))
                            ->one();

                        if ($stripeCustomerRecord) {
                            $stripeCustomer = $stripeCustomerRecord->getAttributes();

                            if ($stripeCustomerRecord->stripeCustomerId) {
                                $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

                                Stripe::setApiKey($craftIdConfig['stripeClientSecret']);
                                $customer = Customer::retrieve($stripeCustomerRecord->stripeCustomerId);

                                if ($customer && $customer->default_source) {
                                    $card = $customer->sources->retrieve($customer->default_source);
                                }
                            }
                        }

                        return $this->asJson([
                            'id' => $user->getId(),
                            'name' => $user->getFullName(),
                            'email' => $user->email,
                            'username' => $user->username,
                            'purchasedPlugins' => $purchasedPlugins,
                            'cardNumber' => $user->cardNumber,
                            'cardExpiry' => $user->cardExpiry,
                            'cardCvc' => $user->cardCvc,
                            'businessName' => $user->businessName,
                            'businessVatId' => $user->businessVatId,
                            'businessAddressLine1' => $user->businessAddressLine1,
                            'businessAddressLine2' => $user->businessAddressLine2,
                            'businessCity' => $user->businessCity,
                            'businessState' => $user->businessState,
                            'businessZipCode' => $user->businessZipCode,
                            'businessCountry' => $user->businessCountry,
                            'stripeCustomer' => $stripeCustomer,
                            'card' => $card,
                        ]);
                    }

                    throw new \Exception("Couldn’t retrieve user.");
                }

                throw new \Exception("Insufficient permissions.");
            }

            throw new \Exception("Couldn’t get user identifier.");
        }

        throw new \Exception("Couldn’t get access token.");
        /*
                } catch (\Exception $e) {
                    return $this->asErrorJson($e->getMessage());
                }*/
    }
}
