<?php

namespace craftcom\id\controllers;

use AdamPaterson\OAuth2\Client\Provider\Stripe as StripeOauthProvider;
use Craft;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craft\records\OAuthToken;
use craftcom\id\records\StripeCustomer as StripeCustomerRecord;
use League\OAuth2\Client\Token\AccessToken;
use Stripe\Account;
use Stripe\Customer;
use Stripe\Stripe;
use yii\web\Response;

/**
 * Class StripeController
 *
 * @package craftcom\id\controllers
 */
class StripeController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * OAuth connect to Stripe.
     *
     * @return Response
     */
    public function actionConnect(): Response
    {
        $provider = $this->_getStripeProvider();

        Craft::$app->getSession()->set('stripe.referrer', Craft::$app->getRequest()->getReferrer());

        $options = [];

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return $this->redirect($authorizationUrl);
    }

    /**
     * OAuth callback.
     *
     * @return Response
     */
    public function actionCallback(): Response
    {
        $user = Craft::$app->getUser()->getIdentity();
        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        if (!$customerRecord) {
            $customerRecord = new StripeCustomerRecord();
            $customerRecord->userId = $user->id;
        }


        // Remove existing token

        if ($customerRecord->oauthTokenId) {
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('id', $customerRecord->oauthTokenId))
                ->one();

            if ($tokenRecord) {
                $tokenRecord->delete();
            }
        }


        // Save new token

        $provider = $this->_getStripeProvider();
        $code = Craft::$app->getRequest()->getParam('code');

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        $tokenRecord = new OAuthToken();
        $tokenRecord->userId = Craft::$app->getUser()->getIdentity()->id;
        $tokenRecord->provider = 'Stripe';
        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();

        $resourceOwner = $provider->getResourceOwner($accessToken);

        $customerRecord->oauthTokenId = $tokenRecord->id;
        $customerRecord->stripeAccountId = $resourceOwner->getId();
        $customerRecord->save();

        $referrer = Craft::$app->getSession()->get('stripe.referrer');

        return $this->redirect($referrer);
    }

    /**
     * OAuth disconnect from Stripe.
     *
     * @return Response
     */
    public function actionDisconnect(): Response
    {
        $userId = Craft::$app->getUser()->getIdentity()->id;

        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $userId))
            ->one();

        $tokenRecord = OAuthToken::find()
            ->where(Db::parseParam('id', $customerRecord->oauthTokenId))
            ->one();

        $provider = $this->_getStripeProvider();
        $accessToken = new AccessToken(['access_token' => $tokenRecord->accessToken]);
        $resourceOwner = $provider->getResourceOwner($accessToken);
        $accountId = $resourceOwner->getId();

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        Stripe::setClientId($craftIdConfig['stripeClientId']);
        Stripe::setApiKey($craftIdConfig['stripeClientSecret']);

        $account = Account::retrieve($accountId);
        $account->deauthorize();

        if ($tokenRecord) {
            $tokenRecord->delete();
        }


        if ($customerRecord) {
            $customerRecord->stripeAccountId = null;
            $customerRecord->save();
        }

        return $this->asJson(['success' => true]);
    }

    /**
     * Returns Stripe account for the current user.
     *
     * @return Response
     */
    public function actionAccount(): Response
    {
        $userId = Craft::$app->getUser()->getIdentity()->id;
        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $userId))
            ->one();

        if ($customerRecord && $customerRecord->oauthTokenId) {
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('id', $customerRecord->oauthTokenId))
                ->one();


            Stripe::setApiKey($tokenRecord->accessToken);

            $account = Account::retrieve();

            return $this->asJson($account);
        }

        return $this->asJson(null);
    }

    /**
     * Returns Stripe customer and default card for the current user.
     *
     * @return Response
     */
    public function actionCustomer(): Response
    {
        $user = Craft::$app->getUser()->getIdentity();
        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        $customer = null;

        if ($customerRecord && $customerRecord->stripeCustomerId) {
            $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

            Stripe::setApiKey($craftIdConfig['stripeClientSecret']);
            $customer = Customer::retrieve($customerRecord->stripeCustomerId);
        }

        return $this->asJson([
            'customer' => $customer,
            'card' => ($customer && $customer->default_source ? $customer->sources->retrieve($customer->default_source) : null)
        ]);
    }

    /**
     * Saves a new credit card and sets it as default source for the Stripe customer.
     *
     * @return Response
     */
    public function actionSaveCard(): Response
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        Stripe::setApiKey($craftIdConfig['stripeClientSecret']);

        $user = Craft::$app->getUser()->getIdentity();

        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        if (!$customerRecord) {
            $customerRecord = new StripeCustomerRecord();
            $customerRecord->userId = $user->id;
        }

        if (!$customerRecord->stripeCustomerId) {
            $customer = Customer::create([
                "email" => $user->email,
                "description" => "Customer for ".$user->email,
            ]);
            $customerRecord->stripeCustomerId = $customer->id;
        }

        $customerRecord->save();

        if ($customerRecord->stripeCustomerId) {
            $token = Craft::$app->getRequest()->getParam('token');
            $customer = Customer::retrieve($customerRecord->stripeCustomerId);

            if ($customer->default_source) {
                $customer->sources->retrieve($customer->default_source)->delete();
            }

            $card = $customer->sources->create(['source' => $token]);
            $customer->default_source = $card->id;
            $customer->save();

            return $this->asJson(['card' => $card]);
        }

        return $this->asErrorJson('Couldn’t save credit card.');
    }

    /**
     * Removes the default credit card from the Stripe customer.
     *
     * @return Response
     */
    public function actionRemoveCard(): Response
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        Stripe::setApiKey($craftIdConfig['stripeClientSecret']);

        $user = Craft::$app->getUser()->getIdentity();

        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        if ($customerRecord && $customerRecord->stripeCustomerId) {
            $customer = Customer::retrieve($customerRecord->stripeCustomerId);

            if ($customer->default_source) {
                $customer->sources->retrieve($customer->default_source)->delete();
                return $this->asJson(['success' => true]);
            }
        }

        return $this->asErrorJson('Couldn’t save credit card.');
    }

    // Private Methods
    // =========================================================================

    /**
     * @return StripeOauthProvider
     */
    private function _getStripeProvider(): StripeOauthProvider
    {
        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        $provider = new StripeOauthProvider([
            'clientId' => $craftIdConfig['stripeClientId'],
            'clientSecret' => $craftIdConfig['stripeClientSecret'],
            'redirectUri' => UrlHelper::actionUrl('id/stripe/callback'),
        ]);

        return $provider;
    }
}
