<?php
namespace craftcom\id\controllers;

use Craft;
use craft\helpers\Db;
use craft\records\OAuthToken;
use craftcom\id\Module;
use craftcom\id\records\StripeCustomer as StripeCustomerRecord;
use Imagine\Filter\Basic\Strip;
use yii\web\Response;
use Exception;
use AdamPaterson\OAuth2\Client\Provider\Stripe as StripeOauthProvider;
use craft\helpers\UrlHelper;
use Stripe\Account;
use Stripe\Customer;
use Stripe\Stripe;

/**
 * Class StripeController
 *
 * @package craftcom\id\controllers
 */
class StripeController extends BaseApiController
{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_clientId = 'ca_2b3yXOngHtKxb4cDEGHeCMhrNwXyWvu5';

    /**
     * @var string
     */
    private $_clientSecret = 'sk_test_FgnfF68q9L8Hp3RRDETaJefc';

    // Public Methods
    // =========================================================================

    /**
     * Connect.
     *
     * @return \yii\web\Response
     */
    public function actionConnect()
    {
        $provider = $this->_getProvider();

        Craft::$app->getSession()->set('stripe.referrer', Craft::$app->getRequest()->getReferrer());

        $options = [];

        $authorizationUrl = $provider->getAuthorizationUrl($options);

        return $this->redirect($authorizationUrl);
    }

    /**
     * Callback.
     *
     * @return \yii\web\Response
     */
    public function actionCallback()
    {
        $user = Craft::$app->getUser()->getIdentity();
        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        if(!$customerRecord) {
            $customerRecord = new StripeCustomerRecord();
            $customerRecord->userId = $user->id;
        }


        // Remove existing token

        if($customerRecord->oauthTokenId) {
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('id', $customerRecord->oauthTokenId))
                ->one();

            if($tokenRecord) {
                $tokenRecord->delete();
            }
        }


        // Save new token

        $provider = $this->_getProvider();
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
     * Handles /stripe/disconnect requests.
     *
     * @return Response
     */
    public function actionDisconnect(): Response
    {
        $userId = Craft::$app->getUser()->getIdentity()->id;
        $token = $this->getOauthToken($userId);

        if($token) {
            $token->delete();
        }

        return $this->asJson(['success' => true]);
    }

    /**
     * Handles /stripe/account requests.
     *
     * @return Response
     */
    public function actionAccount(): Response
    {
        $userId = Craft::$app->getUser()->getIdentity()->id;
        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $userId))
            ->one();

        if($customerRecord && $customerRecord->oauthTokenId) {
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
     * Handles /stripe/customer requests.
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

        if($customerRecord) {
            Stripe::setApiKey($this->_clientSecret);
            $customer = Customer::retrieve($customerRecord->stripeCustomerId);
        }

        return $this->asJson([
            'customer' => $customer,
            'card' => ($customer && $customer->default_source ? $customer->sources->retrieve($customer->default_source) : null)
        ]);
    }

    public function actionSaveCreditCard()
    {
        Stripe::setApiKey($this->_clientSecret);

        $user = Craft::$app->getUser()->getIdentity();

        $customerRecord = StripeCustomerRecord::find()
            ->where(Db::parseParam('userId', $user->id))
            ->one();

        if(!$customerRecord) {
            $customer = Customer::create(array(
                "email" => $user->email,
                "description" => "Customer for ".$user->email,
            ));

            $customerRecord = new StripeCustomerRecord();
            $customerRecord->userId = $user->id;
            $customerRecord->stripeCustomerId = $customer->id;
            $customerRecord->save();
        }

        if($customerRecord->stripeCustomerId) {
            $token = Craft::$app->getRequest()->getParam('token');
            $customer = Customer::retrieve($customerRecord->stripeCustomerId);

            if($customer->default_source) {
                $customer->sources->retrieve($customer->default_source)->delete();
            }

            $card = $customer->sources->create(array('source' => $token));
            $customer->default_source = $card->id;
            $customer->save();

            return $this->asJson(['card' => $card]);
        }
    }

    // Private Methods
    // =========================================================================

    private function getOauthToken($userId)
    {
        return $accessToken = OAuthToken::find()
            ->where(Db::parseParam('userId', $userId))
            ->andWhere(Db::parseParam('provider', 'Stripe'))
            ->one();
    }

    private function _getProvider()
    {
        $provider = new StripeOauthProvider([
            'clientId' => $this->_clientId,
            'clientSecret' => $this->_clientSecret,
            'redirectUri' => UrlHelper::actionUrl('id/stripe/callback'),
        ]);

        return $provider;
    }
}
