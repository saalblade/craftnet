<?php

namespace craftcom\id\controllers;

use Craft;
use craft\records\OAuthToken;
use League\OAuth2\Client\Provider\Exception\GithubIdentityProviderException;
use League\OAuth2\Client\Provider\Github;
use yii\web\Response;
use craft\db\Query;

/**
 * Class ConnectController
 *
 * @package craftcom\id\controllers
 */
class ConnectController extends BaseApiController
{
    /**
     * @var string
     */
    private $_authorizeUrl = 'https://github.com/login/oauth/authorize';

    /**
     * @var string
     */
    private $_tokenUrl = 'https://github.com/login/oauth/access_token';

    /**
     * @var string
     */
    private $_scope = ['user:email', 'write:repo_hook'];

    /**
     * @var string
     */
    private $_clientId = 'b69e4b894ebf1c020d30';

    private $_clientSecret = 'e2085a11212f4259c2243f50bf286b3dfd767d73';

    private $_accessToken;

    private $_connectUri = 'test/developer/connect';

    public function init()
    {
        $this->requireLogin();

        if (
            stripos(Craft::$app->getRequest()->getFullPath(), 'test/developer/connect') !== false &&
            stripos(Craft::$app->getRequest()->getFullPath(), 'test/developer/validate') !== false)
        {
            $token = $this->_getAuthTokenByUserId(Craft::$app->getUser()->getIdentity()-id);

            if (!$token) {
                return $this->redirect($this->_connectUri);
            }

            $this->_accessToken = $token;
        }

        parent::init();
    }

    public function actionConnect(): Response
    {
        $provider = $this->_getProvider();

        $options = [
            'scope' => $this->_scope,
        ];

        $authUrl = $provider->getAuthorizationUrl($options);
        Craft::$app->getSession()->set('oauth2state', $provider->getState());

        return $this->renderTemplate('account/developer/_connect', ['url' => $authUrl]);
    }


    public function actionValidate(): Response
    {
        $code = Craft::$app->getRequest()->getParam('code');
        $state = Craft::$app->getRequest()->getParam('state');

        if (!$code || !$state) {
            Craft::error('Either the code or the oauth2state param was missing in the Github callback.', __METHOD__);
            throw new GithubIdentityProviderException('There was a problem getting an authorzation token.', __METHOD);
        }

        if ($state !== Craft::$app->getSession()->get('oauth2state')) {
            Craft::error('oauth2state was missing in session from the Github callback.', __METHOD__);
            throw new GithubIdentityProviderException('There was a problem getting an authorzation token.', __METHOD__);
        }

        $provider = $this->_getProvider();

        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $code,
            ]);
        } catch (\Exception $e) {
            Craft::error('There was a problem getting an authorization token.', __METHOD__);
            return $this->redirect($this->_connectUri);
        }

        $currentUser = Craft::$app->getUser()->getIdentity();
        $existingToken = $this->_getAuthTokenByUserId($currentUser->id);

        // No previous acces token, create a new one.
        if (!$existingToken) {
            $tokenRecord = new OAuthToken();
            $tokenRecord->userId = $currentUser->id;
            $tokenRecord->provider = 'Github';

        } else {
            // A previous one, let's update it.
            $tokenRecord = OAuthToken::find()
                ->where(Db::parseParam('accessToken', $existingToken))
                ->one();
        }

        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();

        return $this->redirect('test/developer/gettoken');
    }

    public function actionGetToken(): Response
    {
        $currentUser = Craft::$app->getUser()->getIdentity();
        $token = $this->_getAuthTokenByUserId($currentUser->id);

        if (!$token) {
            return $this->redirect($this->_connectUri);
        }

        return $this->renderTemplate('account/developer/_gettoken', ['user' => $currentUser->getFriendlyName(), 'token' => $token]);
    }

    public function actionHooks(): Response
    {
        $response = $this->_callGithub('/repos/takobell/Stringy/hooks');


//        $request = $provider->getAuthenticatedRequest(
        //          'POST',
        //        $provider->apiDomain.'/repos/takobell/Stringy/hooks',
        //      $token
        //);

//        $params = [
        //          'name' => 'web',
        //        'events' => ['push'],
        //      'active' => true,
        //    'config' => [
        //      'url' => 'https://id.craftcms.com',
        //    'content_type' => 'json',
//            ],
        //      ];

//        $body = \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\json_encode($params));
        //      $request = $request->withBody($body);
        //    $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');


//        $request = $provider->getAuthenticatedRequest(
        //          'GET',
        //        $provider->apiDomain.'/repos/takobell/Stringy/hooks',
        //      $token
        //);


//        $response = $provider->getParsedResponse($request);


        //      $body = (string)$response->getBody();

        return $this->renderTemplate('account/developer/listhooks', ['hooks' => $body]);
    }

    private function _getAuthTokenByUserId(int $userId)
    {
        return (new Query())
            ->select(['accessToken'])
            ->from(['oauthtokens'])
            ->where(['userId' => $userId])
            ->scalar();
    }

    private function _getProvider()
    {
        return new Github([
            'clientId' => $this->_clientId,
            'clientSecret' => $this->_clientSecret,
        ]);
    }

    private function _callGithub($url)
    {
        try {
            $provider = $this->_getProvider();

            $request = $provider->getAuthenticatedRequest(
                'GET',
                $provider->apiDomain.$url,
                $this->_accessToken
            );

            return $provider->getParsedResponse($request);

        } catch(GithubIdentityProviderException $e) {
            // The token is no longer valid, let's reconnect.
            return $this->redirect($this->_connectUri);
        }
    }
}
