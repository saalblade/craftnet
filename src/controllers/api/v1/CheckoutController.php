<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\elements\Entry;
use craft\helpers\Db;
use craft\helpers\Json;
use craftcom\controllers\api\BaseApiController;
use craftcom\records\StripeCustomer as StripeCustomerRecord;
use Stripe\Customer;
use Stripe\Stripe;
use yii\web\Response;

/**
 * Class CheckoutController
 *
 * @package craftcom\controllers\api\v1
 */
class CheckoutController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/checkout requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $userId = Craft::$app->getRequest()->getParam('craftId');
        $identity = Craft::$app->getRequest()->getParam('identity');
        $cardToken = Craft::$app->getRequest()->getParam('cardToken');
        $replaceCard = Craft::$app->getRequest()->getParam('replaceCard');
        $cartItems = Craft::$app->getRequest()->getParam('cartItems');

        $entry = new Entry();
        $entry->sectionId = 4;
        $entry->typeId = 6;
        $entry->authorId = 3;

        if ($userId) {
            $entry->customer = [$userId];

            if ($replaceCard && $cardToken) {
                $stripeCustomerRecord = StripeCustomerRecord::find()
                    ->where(Db::parseParam('userId', $userId))
                    ->one();

                if ($stripeCustomerRecord) {
                    if ($stripeCustomerRecord->stripeCustomerId) {
                        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

                        Stripe::setApiKey($craftIdConfig['stripeApiKey']);
                        $customer = Customer::retrieve($stripeCustomerRecord->stripeCustomerId);

                        if ($customer->default_source) {
                            $customer->sources->retrieve($customer->default_source)->delete();
                        }

                        $card = $customer->sources->create(['source' => $cardToken]);
                        $customer->default_source = $card->id;
                        $customer->save();
                    }
                }
            }
        } else {
            if (isset($identity['fullName'])) {
                $entry->customerName = $identity['fullName'];
            }

            if (isset($identity['email'])) {
                $entry->customerEmail = $identity['email'];
            }
        }

        if ($cardToken) {
            $entry->cardToken = $cardToken;
        }

        if ($cartItems) {
            $items = [];
            foreach ($cartItems as $item) {
                $items[] = $item['id'];
            }
            $entry->items = $items;
        }

        if (Craft::$app->getElements()->saveElement($entry)) {
            return $this->asJson($entry);
        }

        $errors = Json::encode($entry->getErrors());

        return $this->asErrorJson($errors);
    }
}
