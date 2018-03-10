<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craftcom\errors\ValidationException;
use Stripe\Error\InvalidRequest;
use yii\base\Exception;
use yii\base\UserException;
use yii\web\Response;

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
        if ($cart->getBillingAddress() === null) {
            $errors[] = [
                'param' => 'orderNumber',
                'message' => 'The cart is missing a billing address',
                'code' => self::ERROR_CODE_INVALID,
            ];
        }

        // make sure the cart isn't empty
        if ($cart->getIsEmpty()) {
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

        // if there are any errors, send them now before the point of no return
        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        // get the gateway
        /** @var StripeGateway $gateway */
        $gateway = $commerce->getGateways()->getGatewayById(getenv('STRIPE_GATEWAY_ID'));

        // pay
        $paymentForm = $gateway->getPaymentFormModel();
        $paymentForm->token = $payload->token;

        if (!$commerce->getPayments()->processPayment($cart, $paymentForm, $redirect, $transaction)) {
            throw new Exception('Payment not processed.');
        }

        /** @var Transaction $transaction */
        return $this->asJson([
            'transaction' => $transaction->toArray(),
        ]);
    }
}
