<?php

namespace craftcom\controllers\api\v1;

use Craft;
use craft\commerce\elements\Order;
use craft\commerce\models\Address;
use craft\commerce\models\Customer;
use craft\commerce\models\LineItem;
use craft\commerce\Plugin as Commerce;
use craft\elements\User;
use craftcom\cms\CmsEdition;
use craftcom\cms\CmsLicenseManager;
use craftcom\controllers\api\BaseApiController;
use craftcom\errors\LicenseNotFoundException;
use craftcom\errors\ValidationException;
use craftcom\helpers\LicenseHelper;
use craftcom\plugins\Plugin;
use Ddeboer\Vatin\Validator;
use Moccalotto\Eu\CountryInfo;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\base\NotSupportedException;
use yii\helpers\ArrayHelper;
use yii\validators\EmailValidator;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class CartsController
 */
class CartsController extends BaseApiController
{
    // Properties
    // =========================================================================

    public $defaultAction = 'create';

    // Public Methods
    // =========================================================================

    /**
     * Creates a cart.
     *
     * @return Response
     */
    public function actionCreate(): Response
    {
        $payload = $this->getPayload('create-cart-request');

        $cart = new Order([
            'number' => Commerce::getInstance()->getCarts()->generateCartNumber(),
            'currency' => 'USD',
            'paymentCurrency' => 'USD',
            'gatewayId' => getenv('STRIPE_GATEWAY_ID'),
        ]);

        $this->_updateCart($cart, $payload);

        return $this->asJson([
            'cart' => $this->cartArray($cart),
            'stripePublicKey' => getenv('STRIPE_PUBLIC_KEY'),
        ]);
    }

    /**
     * Returns cart info.
     *
     * @param string $orderNumber
     * @return Response
     */
    public function actionGet(string $orderNumber): Response
    {
        $cart = $this->getCart($orderNumber);

        return $this->asJson([
            'cart' => $this->cartArray($cart),
            'stripePublicKey' => getenv('STRIPE_PUBLIC_KEY'),
        ]);
    }

    /**
     * Updates a cart.
     *
     * @param string $orderNumber
     * @return Response
     */
    public function actionUpdate(string $orderNumber): Response
    {
        $cart = $this->getCart($orderNumber);
        $payload = $this->getPayload('update-cart-request');
        $this->_updateCart($cart, $payload);

        return $this->asJson([
            'updated' => true,
            'cart' => $this->cartArray($cart),
            'stripePublicKey' => getenv('STRIPE_PUBLIC_KEY'),
        ]);
    }

    /**
     * Deletes a cart.
     *
     * @param string $orderNumber
     * @return Response
     */
    public function actionDelete(string $orderNumber): Response
    {
        $cart = $this->getCart($orderNumber);
        Craft::$app->getElements()->deleteElementById($cart->id);

        return $this->asJson([
            'deleted' => true,
        ]);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @param string $orderNumber
     * @return Order
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    protected function getCart(string $orderNumber): Order
    {
        $cart = Commerce::getInstance()->getOrders()->getOrderByNumber($orderNumber);

        if (!$cart) {
            throw new NotFoundHttpException('Cart Not Found');
        }

        if ($cart->isCompleted) {
            throw new BadRequestHttpException('Cart Already Completed');
        }

        return $cart;
    }

    /**
     * @param Order $cart
     * @return array
     */
    protected function cartArray(Order $cart): array
    {
        return $cart->toArray([], [
            'billingAddress',
            'lineItems.purchasable.plugin'
        ]);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Order $cart
     * @param \stdClass $payload
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    private function _updateCart(Order $cart, \stdClass $payload)
    {
        $commerce = Commerce::getInstance();
        $db = Craft::$app->getDb();

        $errors = [];

        $transaction = $db->beginTransaction();
        try {
            // update the IP
            $cart->lastIp = Craft::$app->getRequest()->getUserIP();

            // set the email/customer before saving the cart, so the cart doesn't create its own customer record
            if (isset($payload->email)) {
                $this->_updateCartEmailAndCustomer($cart, $payload->email, $errors);
            }

            // save the cart if it's new so it gets an ID
            if (!$cart->id && !Craft::$app->getElements()->saveElement($cart)) {
                throw new Exception('Could not save the cart: '.implode(', ', $cart->getErrorSummary(true)));
            }

            // billing address
            if (isset($payload->billingAddressId)) {
                $this->_updateCartBillingAddressId($cart, $payload->billingAddressId, $errors);
            } else if (isset($payload->billingAddress)) {
                $this->_updateCartBillingAddress($cart, $payload->billingAddress, $errors);
            }

            // coupon code
            if (isset($payload->couponCode)) {
                $this->_updateCartCouponCode($cart, $payload->couponCode, $errors);
            }

            // line items
            if (isset($payload->items)) {
                if ($cart->id) {
                    // first clear the cart
                    $commerce->getCarts()->clearCart($cart);
                }

                foreach ($payload->items as $i => $item) {
                    $paramPrefix = "items[{$i}]";

                    // first make sure it validates
                    // todo: eventually we should be able to handle this from the root payload validation, if JSON schemas can do conditional validation
                    if (!$this->validatePayload($item, 'line-item-types/'.$item->type, $errors, $paramPrefix)) {
                        continue;
                    }

                    switch ($item->type) {
                        case 'cms-edition':
                            $lineItem = $this->_cmsEditionLineItem($cart, $item, $paramPrefix, $errors);
                            break;
                        case 'cms-renewal':
                            throw new NotSupportedException('Purchasing CMS renewals is not supported yet.');
                        case 'plugin-edition':
                            $lineItem = $this->_pluginEditionLineItem($cart, $item, $paramPrefix, $errors);
                            break;
                        case 'plugin-renewal':
                            throw new NotSupportedException('Purchasing plugin renewals is not supported yet.');
                        default:
                            $errors[] = [
                                'param' => $paramPrefix.'.type',
                                'message' => "Invalid item type: {$item->type}",
                                'code' => self::ERROR_CODE_INVALID,
                            ];
                            $lineItem = null;
                    }

                    if ($lineItem !== null) {
                        // add a note?
                        if (isset($item->note)) {
                            $lineItem->note = $item->note;
                        }

                        $commerce->getCarts()->addToCart($cart, $lineItem);
                    }
                }
            }

            // were there any validation errors?
            if (!empty($errors)) {
                throw new ValidationException($errors);
            }

            // save the cart
            if (!Craft::$app->getElements()->saveElement($cart)) {
                throw new Exception('Could not save the cart: '.implode(', ', $cart->getErrorSummary(true)));
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * @param Order $cart
     * @param string $email
     * @param array $errors
     * @throws Exception
     */
    private function _updateCartEmailAndCustomer(Order $cart, string $email, array &$errors)
    {
        // validate first
        if (!(new EmailValidator())->validate($email, $error)) {
            $errors[] = [
                'param' => 'email',
                'message' => $error,
                'code' => self::ERROR_CODE_INVALID,
            ];
            return;
        }

        $customersService = Commerce::getInstance()->getCustomers();

        // get the cart's current customer if it has one
        if ($cart->customerId) {
            $currentCustomer = $customersService->getCustomerById($cart->customerId);
        }

        // is this a user account's email?
        $userId = User::find()
            ->select(['elements.id'])
            ->where(['email' => $email])
            ->status(null)
            ->scalar() ?: null;

        if ($userId) {
            // see if we have a customer record for them
            $customer = $customersService->getCustomerByUserId($userId);
        }

        // if the cart is already set to the user's customer, then just leave it alone
        if (isset($customer) && isset($currentCustomer) && $customer->id == $currentCustomer->id) {
            return;
        }

        // is the cart currently set to an anonymous customer?
        if (isset($currentCustomer) && !$currentCustomer->userId) {
            // if we still don't have a user ID, keep using it
            if (!$userId) {
                $customer = $currentCustomer;
            } else {
                // safe to delete it
                $customersService->deleteCustomer($currentCustomer);
            }
        }

        // do we need to create a new customer?
        if (!isset($customer)) {
            $customer = new Customer([
                'userId' => $userId,
            ]);
            if (!$customersService->saveCustomer($customer)) {
                throw new Exception('Could not save the customer: '.implode(' ', $customer->getErrorSummary(true)));
            }
        }

        $cart->customerId = $customer->id;
        $cart->setEmail($email);
    }

    /**
     * @param Order $cart
     * @param int $billingAddressId
     * @param array $errors
     */
    private function _updateCartBillingAddressId(Order $cart, int $billingAddressId, array &$errors)
    {
        // make sure the billing address belongs to the cart's customer
        if (!$cart->customerId) {
            $errors[] = [
                'param' => 'billingAddressId',
                'message' => 'Unable to verify that the billing address is owned by the customer',
                'code' => self::ERROR_CODE_INVALID,
            ];
            return;
        }

        $addresses = Commerce::getInstance()->getAddresses()->getAddressesByCustomerId($cart->customerId);
        $addressIds = ArrayHelper::getColumn($addresses, 'id');

        if (!in_array($billingAddressId, $addressIds, false)) {
            $errors[] = [
                'billingAddressId',
                'message' => 'Billing address not owned by the customer',
                'code' => self::ERROR_CODE_INVALID,
            ];
            return;
        }

        $cart->billingAddressId = $billingAddressId;
    }

    /**
     * @param Order $cart
     * @param \stdClass $billingAddress
     * @param array $errors
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    private function _updateCartBillingAddress(Order $cart, \stdClass $billingAddress, array &$errors)
    {
        $commerce = Commerce::getInstance();
        $addressErrors = [];

        // get the country
        if (isset($billingAddress->country)) {
            if (($country = $commerce->getCountries()->getCountryByIso($billingAddress->country)) === null) {
                $addressErrors[] = [
                    'param' => 'billingAddress.country',
                    'message' => 'Invalid country',
                    'code' => self::ERROR_CODE_INVALID,
                ];
            } else if ((new CountryInfo())->isEuMember($country->iso)) {
                // Make sure they've supplied a VAT ID
                if (!isset($billingAddress->businessTaxId)) {
                    $addressErrors[] = [
                        'param' => 'billingAddress.businessTaxId',
                        'message' => 'A valid VAT ID is required for European orders.',
                        'code' => self::ERROR_CODE_MISSING_FIELD,
                    ];
                } else if (!(new Validator())->isValid($billingAddress->businessTaxId)) {
                    $addressErrors[] = [
                        'param' => 'billingAddress.businessTaxId',
                        'message' => 'A valid VAT ID is required for European orders.',
                        'code' => self::ERROR_CODE_INVALID,
                    ];
                }
            }

            // get the state
            if (!empty($billingAddress->state)) {
                if (($state = $commerce->getStates()->getStateByAbbreviation($country->id, $billingAddress->state)) === null) {
                    $addressErrors[] = [
                        'param' => 'billingAddress.state',
                        'message' => 'Invalid state',
                        'code' => self::ERROR_CODE_INVALID,
                    ];
                }
            } else if ($country !== null && $country->isStateRequired) {
                $addressErrors[] = [
                    'param' => 'billingAddress.state',
                    'message' => "{$country->name} addresses must specify a state.",
                    'code' => self::ERROR_CODE_MISSING_FIELD,
                ];
            }
        }

        // is a billing address already set on the order?
        if ($cart->billingAddressId) {
            $address = $commerce->getAddresses()->getAddressById($cart->billingAddressId);

            // make sure it isn't associated with the customer yet
            if ($address && $cart->customerId) {
                $addresses = $commerce->getAddresses()->getAddressesByCustomerId($cart->customerId);
                $addressIds = ArrayHelper::getColumn($addresses, 'id');
                if (in_array($address->id, $addressIds, false)) {
                    $address = null;
                }
            }
        }

        if (empty($address)) {
            $address = new Address();
        }

        // populate the address
        $addressConfig = [
            'firstName' => $billingAddress->firstName,
            'lastName' => $billingAddress->lastName,
            'attention' => $billingAddress->attention ?? null,
            'title' => $billingAddress->title ?? null,
            'address1' => $billingAddress->address1 ?? null,
            'address2' => $billingAddress->address2 ?? null,
            'city' => $billingAddress->city ?? null,
            'zipCode' => $billingAddress->zipCode ?? null,
            'phone' => $billingAddress->phone ?? null,
            'alternativePhone' => $billingAddress->alternativePhone ?? null,
            'businessName' => $billingAddress->businessName ?? null,
            'businessId' => $billingAddress->businessId ?? null,
            'businessTaxId' => $billingAddress->businessTaxId ?? null,
        ];

        Craft::configure($address, $addressConfig);

        if (!$address->validate(array_keys($addressConfig))) {
            array_push($addressErrors, ...$this->modelErrors($address, 'billingAddress'));
        }

        if (!empty($addressErrors)) {
            array_push($errors, ...$addressErrors);
            return;
        }

        $address->countryId = $country->id ?? null;
        $address->stateId = $state->id ?? null;
        $address->stateName = $state->abbreviation ?? null; // todo: verify this is right

        // save the address
        if (!$commerce->getAddresses()->saveAddress($address)) {
            throw new Exception('Could not save address: '.implode(', ', $address->getErrorSummary(true)));
        }

        // update the cart
        $cart->setBillingAddress($address);
        $cart->billingAddressId = $address->id;
    }

    /**
     * @param Order $cart
     * @param string $couponCode
     * @param array $errors
     */
    private function _updateCartCouponCode(Order $cart, string $couponCode, array &$errors)
    {
        if (!Commerce::getInstance()->getDiscounts()->matchCode($couponCode, $cart->customerId, $explanation)) {
            $errors[] = [
                'param' => 'couponCode',
                'message' => $explanation,
                'code' => self::ERROR_CODE_INVALID,
            ];
            return;
        }

        $cart->couponCode = $couponCode;
    }

    /**
     * @param Order $cart
     * @param \stdClass $item
     * @param string $paramPrefix
     * @param $errors
     * @return LineItem|null
     */
    private function _cmsEditionLineItem(Order $cart, \stdClass $item, string $paramPrefix, &$errors)
    {
        // Get the existing license
        try {
            $license = $this->module->getCmsLicenseManager()->getLicenseByKey($item->licenseKey);
        } catch (LicenseNotFoundException $e) {
            $errors[] = [
                'param' => "{$paramPrefix}.licenseKey",
                'message' => $e->getMessage(),
                'code' => self::ERROR_CODE_MISSING,
            ];
            return null;
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
            $errors[] = [
                'param' => "{$paramPrefix}.edition",
                'message' => "Invalid upgrade edition: {$item->edition}",
                'code' => self::ERROR_CODE_INVALID,
            ];
            return null;
        }

        $edition = CmsEdition::find()
            ->handle($item->edition)
            ->one();

        return Commerce::getInstance()->getLineItems()->resolveLineItem($cart, $edition->id, [
            'licenseKey' => $item->licenseKey,
        ]);
    }

    /**
     * @param Order $cart
     * @param \stdClass $item
     * @param string $paramPrefix
     * @param $errors
     * @return LineItem|null
     */
    private function _pluginEditionLineItem(Order $cart, \stdClass $item, string $paramPrefix, &$errors)
    {
        // get the plugin
        $plugin = Plugin::find()
            ->handle($item->plugin)
            ->one();

        if (!$plugin) {
            $errors[] = [
                'param' => "{$paramPrefix}.plugin",
                'message' => "Invalid plugin handle: {$item->plugin}",
                'code' => self::ERROR_CODE_MISSING,
            ];
            return null;
        }

        // get the edition
        try {
            $edition = $plugin->getEdition($item->edition);
        } catch (InvalidArgumentException $e) {
            $errors[] = [
                'param' => "{$paramPrefix}.edition",
                'message' => $e->getMessage(),
                'code' => self::ERROR_CODE_MISSING,
            ];
            return null;
        }

        // get the license (if there is one)
        if (!empty($item->licenseKey)) {
            try {
                $license = $this->module->getPluginLicenseManager()->getLicenseByKey($item->plugin, $item->licenseKey);
            } catch (LicenseNotFoundException $e) {
                $errors[] = [
                    'param' => "{$paramPrefix}.licenseKey",
                    'message' => $e->getMessage(),
                    'code' => self::ERROR_CODE_MISSING,
                ];
                return null;
            }

            // todo: verify that this is actually an upgrade
            // ...

            $options = [
                'licenseKey' => $license->key,
            ];
        } else {
            // get the Craft license if specified
            if (!empty($item->cmsLicenseKey)) {
                try {
                    $cmsLicense = $this->module->getCmsLicenseManager()->getLicenseByKey($item->cmsLicenseKey);
                } catch (LicenseNotFoundException $e) {
                    $errors[] = [
                        'param' => "{$paramPrefix}.cmsLicenseKey",
                        'message' => $e->getMessage(),
                        'code' => self::ERROR_CODE_MISSING,
                    ];
                    return null;
                }
            } else {
                $cmsLicense = null;
            }

            // generate a license key now to ensure that the line item options are unique
            $options = [
                'licenseKey' => 'new:'.LicenseHelper::generatePluginKey(),
                'cmsLicenseKey' => $cmsLicense->key ?? null,
            ];
        }

        return Commerce::getInstance()->getLineItems()->resolveLineItem($cart, $edition->id, $options);
    }
}
