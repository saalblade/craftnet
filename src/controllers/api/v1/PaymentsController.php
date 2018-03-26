<?php

namespace craftnet\controllers\api\v1;

use Craft;
use craft\commerce\errors\PaymentException;
use craft\commerce\models\Transaction;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craftnet\errors\ValidationException;
use yii\base\Exception;
use yii\base\UserException;
use yii\web\BadRequestHttpException;
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
     * @throws ValidationException if the order number isn't valid or isn't ready to be purchased
     * @throws BadRequestHttpException if there was an issue with the payment
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
        if (round($payload->expectedPrice) < round($totalPrice)) {
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

        // only process a payment if there's a price
        if ($totalPrice) {
            // get the gateway
            /** @var StripeGateway $gateway */
            $gateway = $commerce->getGateways()->getGatewayById(getenv('STRIPE_GATEWAY_ID'));

            // pay
            $paymentForm = $gateway->getPaymentFormModel();
            $paymentForm->token = $payload->token;

            try {
                $commerce->getPayments()->processPayment($cart, $paymentForm, $redirect, $transaction);
            } catch (PaymentException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        } else {
            // just mark it as complete since it's a free order
            $cart->markAsComplete();
        }

        /** @var Transaction $transaction */
        $response = ['completed' => true];
        if (isset($transaction)) {
            $response['transaction'] = $transaction->toArray();
        }
        return $this->asJson($response);
    }
}
