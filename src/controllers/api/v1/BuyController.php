<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Address;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\helpers\LicenseHelper;
use craftcom\plugins\Plugin;
use craftcom\plugins\PluginLicense;
use Stripe\Source;
use Stripe\Stripe;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\web\BadRequestHttpException;
use yii\web\Response;

/**
 * Buy Controller
 *
 * @package craftcom\controllers\api\v1
 */
class BuyController extends BaseApiController
{
    // Public Methods
    // =========================================================================

    /**
     * Handles /v1/buy requests.
     *
     * @return Response
     * @throws \Throwable if reasons
     */
    public function actionIndex(): Response
    {
        $payload = $this->getPayload('buy-request');
        $commerce = Commerce::getInstance();

        // create & validate the models ----------------------------------------

        $errors = [];

        // email validation
        $emailValidator = new EmailValidator(['skipOnEmpty' => false]);
        if (!$emailValidator->validate($payload->email, $error)) {
            $errors['email'][] = $error;
        }

        // create the customer
//        $customer = new Customer([
//            'email' => $payload->email,
//        ]);

        // get the country
        if (($country = $commerce->getCountries()->getCountryByIso($payload->billingAddress->country)) === null) {
            $errors['billingAddress']['country'][] = 'Invalid country';
        }

        // get the state
        if (!empty($payload->billingAddress->state)) {
            if (($state = $commerce->getStates()->getStateByAbbreviation($country->id, $payload->billingAddress->state)) === null) {
                $errors['billingAddress']['state'][] = 'Invalid state';
            }
        }

        // create the address
        $addressConfig = [
            'firstName' => $payload->billingAddress->firstName,
            'lastName' => $payload->billingAddress->lastName,
            'address1' => $payload->billingAddress->address1,
            'address2' => $payload->billingAddress->address2,
            'city' => $payload->billingAddress->city,
            'zipCode' => $payload->billingAddress->zipCode,
            'businessName' => $payload->billingAddress->businessName,
            'businessTaxId' => $payload->billingAddress->businessTaxId,
        ];
        $address = new Address($addressConfig);
        if (!$address->validate(array_keys($addressConfig))) {
            $errors = ArrayHelper::merge($errors, [
                'billingAddress' => $address->getFirstErrors()
            ]);
        }
        $address->countryId = $country->id;
        $address->stateId = $state->id ?? null;
        $address->stateName = $state->abbreviation ?? null; // todo: verify this is right

        // coupon code validation
        if (
            !empty($payload->couponCode) &&
            !$commerce->getDiscounts()->matchCode($payload->couponCode, null, $error)
        ) {
            $errors['couponCode'][] = $error;
        }

        // begin a transaction
        $db = Craft::$app->getDb();
        $transaction = $db->beginTransaction();

        try {
            // save the address
            if (!$commerce->getAddresses()->saveAddress($address)) {
                Craft::error('Could not save address: '.implode(', ', $address->getFirstErrors()), __METHOD__);
                throw new Exception('Order not placed.');
            }

            // get the gateway
            /** @var StripeGateway $gateway */
            $gateway = $commerce->getGateways()->getGatewayById(getenv('STRIPE_GATEWAY_ID'));

            // create & save the order
            $orderNumber = $commerce->getCarts()->generateCartNumber();
            // todo: getUserIP() the right call here?
            $order = new Order([
                'number' => $orderNumber,
                'email' => $payload->email,
                'currency' => 'USD',
                'paymentCurrency' => 'USD',
                'lastIp' => Craft::$app->getRequest()->getUserIP(),
                'billingAddressId' => $address->id,
                'gatewayId' => $gateway->id,
                'couponCode' => $payload->couponCode ?? null,
            ]);

            if (!Craft::$app->getElements()->saveElement($order)) {
                Craft::error('Could not save order: '.implode(', ', $order->getFirstErrors()), __METHOD__);
                throw new Exception('Order not placed.');
            }

            /** @var PluginLicense[] $newPluginLicenses */
            $newPluginLicenses = [];

            foreach ($payload->items as $i => $item) {
                switch ($item->type) {
                    case 'cms-edition':
                        // Get the existing license
                        try {
                            $license = $this->module->getCmsLicenseManager()->getLicenseByKey($item->licenseKey);
                        } catch (LicenseNotFoundException $e) {
                            $errors['items'][$i]['licenseKey'][] = $e->getMessage();
                            break;
                        }

                        // Make sure this is actually an upgrade
                        switch ($license->edition) {
                            case CmsLicenseManager::EDITION_PERSONAL:
                                $validUpgrades = [CmsLicenseManager::EDITION_CLIENT, CmsLicenseManager::EDITION_PRO];
                                break;
                            case CmsLicenseManager::EDITION_CLIENT:
                                $validUpgrades = [CmsLicenseManager::EDITION_PRO];
                                break;
                            default:
                                $validUpgrades = [];
                        }

                        if (!in_array($item->edition, $validUpgrades, true)) {
                            $errors['items'][$i]['edition'][] = "Invalid upgrade edition: {$item->edition}";
                            break;
                        }

                        $edition = CmsEdition::find()
                            ->handle($item->edition)
                            ->one();

                        $lineItem = $commerce->getLineItems()->resolveLineItem($order, $edition->id, [
                            'licenseKey' => $item->licenseKey,
                        ]);

                        break;

                    case 'cms-renewal':
                        throw new NotSupportedException('Purchasing CMS renewals is not supported yet.');

                    case 'plugin-edition':
                        // get the plugin
                        $plugin = Plugin::find()
                            ->handle($item->plugin)
                            ->one();
                        if (!$plugin) {
                            $errors['items'][$i]['plugin'][] = "Invalid plugin handle: {$item->plugin}";
                            break;
                        }

                        try {
                            $edition = $plugin->getEdition($item->edition);
                        } catch (InvalidArgumentException $e) {
                            $errors['items'][$i]['edition'][] = "Invalid plugin edition: {$item->edition}";
                            break;
                        }

                        // get the license (if there is one)
                        if (!empty($item->licenseKey)) {
                            try {
                                $license = $this->module->getPluginLicenseManager()->getLicenseByKey($item->licenseKey);
                            } catch (LicenseNotFoundException $e) {
                                $errors['items'][$i]['licenseKey'][] = $e->getMessage();
                                break;
                            }
                        } else {
                            // get the Craft license if specified
                            if (!empty($item->cmsLicenseKey)) {
                                try {
                                    $cmsLicense = $this->module->getCmsLicenseManager()->getLicenseByKey($item->cmsLicenseKey);
                                } catch (LicenseNotFoundException $e) {
                                    $errors['items'][$i]['cmsLicenseKey'][] = $e->getMessage();
                                    break;
                                }
                            } else {
                                $cmsLicense = null;
                            }

                            // create a license
                            $license = $newPluginLicenses[] = new PluginLicense([
                                'pluginId' => $plugin->id,
                                'editionId' => $edition->id,
                                'cmsLicenseId' => $cmsLicense->id ?? null,
                                'expirable' => true,
                                'expired' => false,
                                'email' => $payload->email,
                                'key' => LicenseHelper::generateKey(24),
                            ]);

                            if (!$this->module->getPluginLicenseManager()->saveLicense($license)) {
                                throw new Exception('Could not create plugin license: '.implode(',', $license->getFirstErrors()));
                            }
                        }

                        // todo: verify that this is actually an upgrade

                        $lineItem = $commerce->getLineItems()->resolveLineItem($order, $edition->id, [
                            'licenseKey' => $license->key,
                        ]);

                        break;

                    case 'plugin-renewal':
                        throw new NotSupportedException('Purchasing plugin renewals is not supported yet.');

                    default:
                        throw new BadRequestHttpException("Invalid item type: {$item->type}");
                }

                $commerce->getCarts()->addToCart($order, $lineItem);
            }

            // make sure the cost is in line with what they were expecting
            $totalPrice = $order->getTotalPrice();
            if ($payload->totalPrice < $totalPrice) {
                $formatter = Craft::$app->getFormatter();
                $fmtExpected = $formatter->asCurrency($payload->totalPrice, 'USD', [], [], true);
                $fmtActual = $formatter->asCurrency($totalPrice, 'USD', [], [], true);
                $errors['totalPrice'][] = "Expected price ({$fmtExpected}) was less than the order total ({$fmtActual}).";
            }

            // if there are any errors, bail before we get to the point of no return
            if (!empty($errors)) {
                $transaction->rollBack();
                Craft::$app->getResponse()->setStatusCode(400);
                return $this->asJson(compact('errors'));
            }

            // create a source token
            Stripe::setApiKey(getenv('STRIPE_API_KEY'));
            /** @var Source $source */
            $source = Source::create([
                'type' => 'card',
                'token' => $payload->cc->token,
            ]);

            // pay
            $paymentForm = $gateway->getPaymentFormModel();
            $paymentForm->firstName = $address->firstName;
            $paymentForm->lastName = $address->lastName;
            $paymentForm->month = $payload->cc->expMonth;
            $paymentForm->year = $payload->cc->expYear;
            $paymentForm->token = $source->id;

            if (!$commerce->getPayments()->processPayment($order, $paymentForm, $redirect, $paymentTransaction)) {
                throw new Exception('Payment not processed.');
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->asJson($order->toArray([], [
            'lineItems.purchasable.plugin'
        ]));
    }
}
