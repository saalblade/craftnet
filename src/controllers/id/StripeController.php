<?php

namespace craftcom\controllers\id;

use AdamPaterson\OAuth2\Client\Provider\Stripe as StripeOauthProvider;
use Craft;
use craft\commerce\base\Gateway;
use craft\commerce\Plugin as Commerce;
use craft\helpers\Db;
use craft\helpers\UrlHelper;
use craftcom\records\StripeCustomer as StripeCustomerRecord;
use craftcom\records\VcsToken;
use League\OAuth2\Client\Token\AccessToken;
use Stripe\Account;
use Stripe\Stripe;
use yii\helpers\Json;
use yii\web\HttpException;
use yii\web\Response;



/**
 * Class StripeController
 *
 * @package craftcom\controllers\id
 */
class StripeController extends BaseController
{
    // Properties
    // =========================================================================

    /**
     * @var int
     */
    private $gatewayId = 2;

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
        $options = [];

        Craft::$app->getSession()->set('stripe.referrer', Craft::$app->getRequest()->getReferrer());
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
            $tokenRecord = VcsToken::find()
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

        $tokenRecord = new VcsToken();
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

        $tokenRecord = VcsToken::find()
            ->where(Db::parseParam('id', $customerRecord->oauthTokenId))
            ->one();

        $provider = $this->_getStripeProvider();
        $accessToken = new AccessToken(['access_token' => $tokenRecord->accessToken]);
        $resourceOwner = $provider->getResourceOwner($accessToken);
        $accountId = $resourceOwner->getId();

        $craftIdConfig = Craft::$app->getConfig()->getConfigFromFile('craftid');

        Stripe::setClientId($craftIdConfig['stripeClientId']);
        Stripe::setApiKey($craftIdConfig['stripeSecretKey']);

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
            $tokenRecord = VcsToken::find()
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
        $customer = \craft\commerce\stripe\Plugin::getInstance()->getCustomers()->getCustomer($this->gatewayId, $user->id);

        $paymentSource = null;
        $card = null;
        $paymentSources = \craft\commerce\Plugin::getInstance()->getPaymentSources()->getAllPaymentSourcesByUserId($user->id);

        if(count($paymentSources)) {
            $paymentSource = $paymentSources[0];
            $response = Json::decode($paymentSource->response);

            if(isset($response['card'])) {
                $card = $response['card'];
            } elseif(isset($response['object']) && $response['object'] === 'card') {
                $card = $response;
            }
        }

        return $this->asJson([
            'customer' => $customer,
            'paymentSource' => $paymentSource,
            'card' => $card,
        ]);
    }

    /**
     * Saves a new credit card and sets it as default source for the Stripe customer.
     *
     * @return Response|null
     * @throws \Throwable if something went wrong when adding the payment source
     */
    public function actionSaveCard(): Response
    {
        $this->requirePostRequest();

        $order = null;

        $plugin = Commerce::getInstance();
        $request = Craft::$app->getRequest();
        $paymentSources = $plugin->getPaymentSources();

        // Are we paying anonymously?
        $userId = Craft::$app->getUser()->getId();

        if (!$userId) {
            throw new HttpException(401, Craft::t('commerce', 'Not authorized to save a credit card.'));
        }

        /** @var Gateway $gateway */
        $gateway = $plugin->getGateways()->getGatewayById($this->gatewayId);

        if (!$gateway || !$gateway->supportsPaymentSources()) {
            $error = Craft::t('commerce', 'There is no gateway selected that supports payment sources.');
            return $this->asErrorJson($error);
        }

        // Remove existing payment sources
        $existingPaymentSources = $paymentSources->getAllPaymentSourcesByUserId($userId);

        foreach($existingPaymentSources as $paymentSource) {
            $paymentSources->deletePaymentSourceById($paymentSource->id);
        }

        // Get the payment method' gateway adapter's expected form model
        $paymentForm = $gateway->getPaymentFormModel();
        $paymentForm->setAttributes($request->getBodyParams(), false);
        $description = 'Default Source';

        $error = '';

        try {
            $paymentSource = $paymentSources->createPaymentSource($userId, $gateway, $paymentForm, $description);

            if ($paymentSource) {
               $card = $paymentSource->response;

                return $this->asJson([
                    'success' => true,
                    'card' => $card,
                ]);
            }
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        return $this->asJson(['error' => $error, 'paymentForm' => $paymentForm->getErrors()]);
    }

    /**
     * Removes the default payment source.
     *
     * @return Response
     */
    public function actionRemoveCard(): Response
    {
        $user = Craft::$app->getUser()->getIdentity();

        $paymentSourcesService = Commerce::getInstance()->getPaymentSources();

        $paymentSources = $paymentSourcesService->getAllPaymentSourcesByUserId($user->id);

        if (count($paymentSources)) {
            $result = $paymentSourcesService->deletePaymentSourceById($paymentSources[0]->id);

            if (!$result) {
                return $this->asErrorJson('Couldn’t delete credit card.');
            }
        }

        return $this->asJson(['success' => true]);
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
            'clientSecret' => $craftIdConfig['stripeSecretKey'],
            'redirectUri' => UrlHelper::actionUrl('craftcom/id/stripe/callback'),
        ]);

        return $provider;
    }
}
