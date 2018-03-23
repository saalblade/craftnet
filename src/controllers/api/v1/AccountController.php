<?php

namespace craftnet\controllers\api\v1;

use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use craftnet\controllers\api\BaseApiController;
use craftnet\oauthserver\Module as OauthServer;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Class AccountController
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
        try {
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
                            $paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

                            if (count($paymentSources) > 0) {
                                $paymentSource = $paymentSources[0];
                                $response = Json::decode($paymentSource->response);

                                if (isset($response['object']) && $response['object'] === 'card') {
                                    $card = $response;
                                }
                            }

                            return $this->asJson([
                                'id' => $user->getId(),
                                'name' => $user->getFullName(),
                                'email' => $user->email,
                                'username' => $user->username,
                                'purchasedPlugins' => $purchasedPlugins,
                                'businessName' => $user->businessName,
                                'businessVatId' => $user->businessVatId,
                                'businessAddressLine1' => $user->businessAddressLine1,
                                'businessAddressLine2' => $user->businessAddressLine2,
                                'businessCity' => $user->businessCity,
                                'businessState' => $user->businessState,
                                'businessZipCode' => $user->businessZipCode,
                                'businessCountry' => $user->businessCountry,
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
        } catch (\Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
