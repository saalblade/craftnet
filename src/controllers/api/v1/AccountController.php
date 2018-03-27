<?php

namespace craftnet\controllers\api\v1;

use Craft;
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
        if (($user = Craft::$app->getUser()->getIdentity(false)) === null) {
            throw new UnauthorizedHttpException('Not Authorized');
        }

        $purchasedPlugins = [];

        foreach ($user->purchasedPlugins->all() as $purchasedPlugin) {
            $purchasedPlugins[] = [
                'name' => $purchasedPlugin->title,
                'developerName' => $purchasedPlugin->getAuthor()->developerName,
                'developerUrl' => $purchasedPlugin->getAuthor()->developerUrl,
            ];
        }

        $card = null;
        $cardToken = null;
        $paymentSources = Commerce::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

        if (count($paymentSources) > 0) {
            $paymentSource = $paymentSources[0];
            $cardToken = $paymentSource->token;
            $response = Json::decode($paymentSource->response);

            if (isset($response['object']) && $response['object'] === 'card') {
                $card = $response;
            } elseif (isset($response['object']) && $response['object'] === 'source') {
                $card = $response['card'];
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
            'cardToken' => $cardToken,
        ]);
    }
}
