<?php

namespace craftnet\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\commerce\Plugin as Commerce;
use craft\commerce\stripe\gateways\Gateway as StripeGateway;
use craft\commerce\stripe\models\forms\Payment;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craftnet\base\LicenseInterface;
use craftnet\Module;
use Stripe\Error\Base as StripeError;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

/**
 * Handles tasks that apply to both Craft and plugin licenses.
 *
 * @property Module $module
 */
class LicensesController extends Controller
{
    /**
     * Sends reminders to people whose Craft/plugin license(s) will be expiring in the next 14-30 days.
     *
     * @return int
     */
    public function actionSendReminders(): int
    {
        $cmsLicenseManager = $this->module->getCmsLicenseManager();
        $pluginLicenseManager = $this->module->getPluginLicenseManager();

        // Find licenses that need reminders
        $this->stdout('Finding licenses that are due for reminders ... ', Console::FG_YELLOW);
        $licenses = array_merge(
            $cmsLicenseManager->getRemindableLicenses(),
            $pluginLicenseManager->getRemindableLicenses()
        );
        $this->stdout('done (' . count($licenses) . ' licenses found)' . PHP_EOL, Console::FG_YELLOW);

        if (empty($licenses)) {
            $this->stdout('Nothing to send.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout('Sending reminders ...' . PHP_EOL, Console::FG_YELLOW);

        // Group by owner (or email if unclaimed)
        $licenses = ArrayHelper::index($licenses, null, function(LicenseInterface $license) {
            $ownerId = $license->getOwnerId();
            return $ownerId ? "owner-{$ownerId}" : mb_strtolower($license->getEmail());
        });

        $mailer = Craft::$app->getMailer();

        foreach ($licenses as $ownerKey => $ownerLicenses) {
            try {
                /** @var string $email */
                /** @var User|null $user */
                list($email, $user) = $this->_resolveOwnerKey($ownerKey);

                // Lock in the renewal prices
                /** @var LicenseInterface[] $ownerLicenses */
                foreach ($ownerLicenses as $license) {
                    if ($license->getWillAutoRenew()) {
                        $newRenewalPrice = $license->getEdition()->getRenewal()->getPrice();
                        if ($license->getRenewalPrice() !== $newRenewalPrice) {
                            $license->setRenewalPrice($newRenewalPrice);
                        }
                    }
                }

                $ownerLicensesByType = ArrayHelper::index($ownerLicenses, null, function(LicenseInterface $license) {
                    return $license->getWillAutoRenew() ? 'auto' : 'manual';
                });

                $this->stdout("    - Emailing {$email} about " . count($ownerLicenses) . ' licenses ... ', Console::FG_YELLOW);

                $message = $mailer
                    ->composeFromKey(Module::MESSAGE_KEY_LICENSE_REMINDER, ['licenses' => $ownerLicensesByType])
                    ->setTo($user ?? $email);

                if (!$message->send()) {
                    $this->stderr('error sending email' . PHP_EOL, Console::FG_RED);
                    continue;
                }

                $this->stdout('done' . PHP_EOL, Console::FG_GREEN);

                // Mark the licenses as reminded so we don't send this again for them until the next cycle
                foreach ($ownerLicenses as $license) {
                    $license->markAsReminded();
                }
            } catch (\Throwable $e) {
                // Don't let this stop us from sending other reminders
                $this->stdout('An error occurred: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);
                Craft::$app->getErrorHandler()->logException($e);
            }
        }

        $this->stdout('Done sending reminders.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Auto-renews or expires licenses that are due for it.
     *
     * @return int
     */
    public function actionProcessExpiredLicenses(): int
    {
        $cmsLicenseManager = $this->module->getCmsLicenseManager();
        $pluginLicenseManager = $this->module->getPluginLicenseManager();

        // Find freshly-expired licenses
        $this->stdout('Finding freshly-expired licenses ... ', Console::FG_YELLOW);
        $licenses = array_merge(
            $cmsLicenseManager->getFreshlyExpiredLicenses(),
            $pluginLicenseManager->getFreshlyExpiredLicenses()
        );
        $this->stdout('done (' . count($licenses) . ' licenses found)' . PHP_EOL, Console::FG_YELLOW);

        if (empty($licenses)) {
            $this->stdout('No licenses have expired.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);
            return ExitCode::OK;
        }

        $this->stdout('Processing licenses ...' . PHP_EOL, Console::FG_YELLOW);

        // Group by owner email
        $licenses = ArrayHelper::index($licenses, null, function(LicenseInterface $license) {
            return mb_strtolower($license->getEmail());
        });

        $utc = new \DateTimeZone('UTC');
        $yesterday = (new \DateTime('-1 day', $utc))->format('Y-m-d');
        $elementsService = Craft::$app->getElements();
        $customersService = Commerce::getInstance()->getCustomers();
        $cartsService = Commerce::getInstance()->getCarts();
        $lineItemsService = Commerce::getInstance()->getLineItems();
        /** @var StripeGateway $gateway */
        $gateway = Commerce::getInstance()->getGateways()->getGatewayById(getenv('STRIPE_GATEWAY_ID'));
        $mailer = Craft::$app->getMailer();

        foreach ($licenses as $email => $ownerLicenses) {
            try {
                $user = User::find()->email($email)->anyStatus()->one();

                // Group by auto-renew status
                $ownerLicensesByType = ArrayHelper::index($ownerLicenses, null, function(LicenseInterface $license) use ($user, $utc, $yesterday) {
                    if ($user && $license->getWillAutoRenew() && $license->getWasReminded()) {
                        // Only auto-renew if it just expired yesterday
                        $expiryDate = $license->getExpiryDate();
                        $expiryDate->setTimezone($utc);
                        if ($expiryDate->format('Y-m-d') === $yesterday) {
                            return 'renew';
                        }
                    }
                    return 'expire';
                });

                // If there are any licenses that should be auto-renewed, give that a shot
                if (!empty($ownerLicensesByType['renew'])) {
                    $this->stdout('    - Creating order for ' . count($ownerLicensesByType['renew']) . " licenses for {$email} ... ", Console::FG_YELLOW);
                    try {
                        $order = new Order([
                            'number' => $cartsService->generateCartNumber(),
                            'currency' => 'USD',
                            'paymentCurrency' => 'USD',
                            'gatewayId' => getenv('STRIPE_GATEWAY_ID'),
                            'orderLanguage' => Craft::$app->language,
                        ]);

                        // Set the customer
                        $customer = null;
                        if ($user) {
                            $customer = $customersService->getCustomerByUserId($user->id);
                        }
                        if ($customer === null) {
                            $customer = new Customer(['userId' => $user->id ?? null]);
                            if (!$customersService->saveCustomer($customer)) {
                                throw new \Exception('Could not save the customer: ' . implode(' ', $customer->getErrorSummary(true)));
                            }
                        }
                        $order->customerId = $customer->id;
                        $order->setEmail($email);

                        // Save the cart so it gets an ID
                        if (!$elementsService->saveElement($order)) {
                            throw new \Exception('Could not save the cart: ' . implode(', ', $order->getErrorSummary(true)));
                        }

                        // Add the line items to the cart
                        foreach ($ownerLicensesByType['renew'] as $license) {
                            /** @var LicenseInterface $license */
                            $renewalId = $license->getEdition()->getRenewal()->getId();
                            $lineItem = $lineItemsService->resolveLineItem($order->id, $renewalId, [
                                'licenseKey' => $license->getKey(),
                                'lockedPrice' => $license->getRenewalPrice(),
                            ]);
                            $lineItem->qty = 1;
                            $order->addLineItem($lineItem);
                        }

                        // Pay for it
                        /** @var Payment $paymentForm */
                        $paymentForm = $gateway->getPaymentFormModel();

                        try {
                            // todo: populate the payment form and process the payment
                            //$this->_populatePaymentForm($payload, $gateway, $paymentForm);
                            //$commerce->getPayments()->processPayment($cart, $paymentForm, $redirect, $transaction);
                        } catch (\Throwable $e) {
                            // todo: handle this
                        }
                    } catch (\Throwable $e) {
                        $this->stderr('error: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);
                        Craft::$app->getErrorHandler()->logException($e);
                    }
                }

                // todo: loop through expired licenses (including ones set to auto-renew if the payment was unsuccessful)
                // and set expired=true, reminded=false

                // todo: update the renewal adjuster to respect the lockedPrice option if set, and set expired=false, reminded=false
            } catch (\Throwable $e) {
                // Don't let this stop us from sending other reminders
                $this->stdout('An error occurred: ' . $e->getMessage() . PHP_EOL, Console::FG_RED);
                Craft::$app->getErrorHandler()->logException($e);
            }
        }

        $this->stdout('Done processing licenses.' . PHP_EOL . PHP_EOL, Console::FG_GREEN);
        return ExitCode::OK;
    }

    /**
     * Returns the email and user account (if one exists) for the given license owner key.
     *
     * @param string $ownerKey
     * @return array
     */
    private function _resolveOwnerKey(string $ownerKey): array
    {
        if (preg_match('/^owner-(\d+)$/', $ownerKey, $matches)) {
            $user = User::find()->id((int)$matches[1])->anyStatus()->one();
            $email = $user->email;
        } else {
            $email = $ownerKey;
            $user = User::find()->email($email)->anyStatus()->one();
        }

        return [$email, $user];
    }
}
