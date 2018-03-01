<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Address;
use craft\commerce\models\Customer;
use craft\commerce\models\LineItem;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\errors\ValidationException;
use craftcom\helpers\LicenseHelper;
use craftcom\plugins\Plugin;
use Stripe\Error\InvalidRequest;
use Stripe\Source;
use Stripe\Stripe;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\base\UserException;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;

/**
 * Class PaymentsController
 */
class PaymentsController extends CartsController
{
    // Properties
    // =========================================================================

    public $defaultAction = 'pay';

    // Public Methods
    // =========================================================================

    /**
     * Processes a payment for an order.
     *
     * @return Response
     * @throws Exception
     * @throws InvalidRequest
     * @throws ValidationException
     * @throws \Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPay(): Response
    {
        $payload = $this->getPayload('payment-request');

        try {
            $cart = $this->getCart($payload->orderNumber);
        } catch (UserException $e) {
            throw new ValidationException([
                [
                    'param' => 'orderNumber',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode() === 404 ? self::ERROR_CODE_MISSING : self::ERROR_CODE_INVALID,
                ]
            ]);
        }

        $errors = [];
        $commerce = Commerce::getInstance();

        // make sure the cart has a billing address
        if (($address = $cart->getBillingAddress()) === null) {
            $errors[] = [
                'param' => 'orderNumber',
                'message' => 'The cart is missing a billing address',
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        // make sure the cart isn't empty
        if ($cart->isEmpty()) {
            $errors[] = [
                'param' => 'orderNumber',
                'message' => 'The cart is empty',
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        // make sure the cost is in line with what they were expecting
        $totalPrice = $cart->getTotalPrice();
        if ($payload->expectedPrice < $totalPrice) {
            $formatter = Craft::$app->getFormatter();
            $fmtExpected = $formatter->asCurrency($payload->expectedPrice, 'USD', [], [], true);
            $fmtTotal = $formatter->asCurrency($totalPrice, 'USD', [], [], true);
            $errors[] = [
                'param' => 'expectedPrice',
                'message' => "Expected price ({$fmtExpected}) was less than the order total ({$fmtTotal}).",
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        // if there are any errors, bail before we bother Stripe for a source token
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // create a source token
        Stripe::setApiKey(getenv('STRIPE_API_KEY'));

        try {
            /** @var Source $source */
            $source = Source::create([
                'type' => 'card',
                'token' => $payload->cc->token,
            ]);
        } catch (InvalidRequest $e) {
            // only surface this to the user if the error is on the CC token
            if ($e->getStripeParam() === 'token') {
                throw new ValidationException([
                    [
                        'param' => 'cc.token',
                        'message' => $e->getMessage(),
                        'code' => self::ERROR_CODE_INVALID,
                    ]
                ], 'Stripe Error', $e->getCode(), $e);
            }
            throw $e;
        }

        // get the gateway
        /** @var StripeGateway $gateway */
        $gateway = $commerce->getGateways()->getGatewayById(getenv('STRIPE_GATEWAY_ID'));

        // pay
        $paymentForm = $gateway->getPaymentFormModel();
        $paymentForm->firstName = $address->firstName;
        $paymentForm->lastName = $address->lastName;
        $paymentForm->month = $payload->cc->expMonth;
        $paymentForm->year = $payload->cc->expYear;
        $paymentForm->token = $source->id;

        if (!$commerce->getPayments()->processPayment($cart, $paymentForm, $redirect, $transaction)) {
            throw new Exception('Payment not processed.');
        }

        /** @var Transaction $transaction */
        return $this->asJson([
            'transaction' => $transaction->toArray(),
        ]);
    }
}
