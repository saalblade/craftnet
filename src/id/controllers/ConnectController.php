<?php

namespace craftcom\id\controllers;

use Craft;
use craft\records\OAuthToken;
use craftcom\id\controllers\BaseApiController;
use craft\helpers\Json;
use function GuzzleHttp\Psr7\stream_for;
use League\OAuth2\Client\Provider\Github;
use yii\web\Response;

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

    /**
     * Handles /connect requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
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
        $state = Craft::$app->getRequest()->getParam('oauth2state');

        if (!$code || !$state) {
            // Exception?
        }

        if ($state !== Craft::$app->getSession()->get('oauth2state')) {
            // Exception?
        }

        $provider = $this->_getProvider();

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        // try/catch

        $user = $provider->getResourceOwner($accessToken);


//        $params = [
  //          'client_id' => $this->_clientId,
    //        'client_secret' => $this->_clientSecret,
      //      'state' => Craft::$app->getSession()->get('oauth2state'),
        //    'code' => $code,
//        ];

        // try/catch
  //      $httpClient = Craft::createGuzzleClient(['headers' => ['Accept' => 'application/json']]);
    //    $response = $httpClient->request('post', $this->_tokenUrl, [
      //      'json' => $params,
//        ]);

//        if ($response->getStatusCode() === 200) {
  //          $responseBody = (string)$response->getBody();
    //        $responseBody = Json::decodeIfJson($responseBody);

            // Something went wrong.
      //      if (!is_array($responseBody)) {
                // something fucked up.
        //    }

//            if (isset($responseBody['error'])) {
                // something gracefully fucked up.
  //          }

    //        $accessToken = $responseBody['access_token'];
//
  //          $client = new \Github\Client();
    //        $client->authenticate($accessToken, null, \Github\Client::AUTH_HTTP_TOKEN);
      //      $test = $client->me()->show();


        $tokenRecord = new OAuthToken();
        $tokenRecord->userId = Craft::$app->getUser()->getIdentity()->id;
        $tokenRecord->provider = 'Github';
        $tokenRecord->accessToken = $accessToken->getToken();
        $tokenRecord->expiresIn = $accessToken->getExpires();
        $tokenRecord->refreshToken = $accessToken->getRefreshToken();
        $tokenRecord->save();




        return $this->renderTemplate('account/developer/_validate', ['user' => $user->getNickname(), 'token' => $accessToken->getToken()]);
        //}

        //$client = new \Github\Client();
        //$test = $client->authenticate($code, null, \Github\Client::AUTH_HTTP_TOKEN);
        //$test2 = $client->me();
    }

    public function actionListRepos(): Response
    {

    }

    public function actionHooks(): Response
    {
        $token = '40f0e01a5100efd3107e276075d8de0e81ba4585';

        $provider = $this->_getProvider();

        //$provider->getParsedResponse()

        $request = $provider->getAuthenticatedRequest(
            'POST',
            $provider->apiDomain.'/repos/takobell/Stringy/hooks',
            $token
        );

        $params = [
            'name' => 'web',
            'events' => ['push'],
            'active' => true,
            'config' => [
                'url' => 'https://id.craftcms.com',
                'content_type' => 'json',
            ],
        ];

        $body = \GuzzleHttp\Psr7\stream_for(\GuzzleHttp\json_encode($params));
        $request = $request->withBody($body);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');



//        $request = $provider->getAuthenticatedRequest(
  //          'GET',
    //        $provider->apiDomain.'/repos/takobell/Stringy/hooks',
      //      $token
        //);


        $response = $provider->getParsedResponse($request);



        $body = (string)$response->getBody();

        return $this->renderTemplate('account/developer/listhooks', ['hooks' => $body]);
    }

    private function _getProvider()
    {
        $provider = new Github([
            'clientId'          => $this->_clientId,
            'clientSecret'      => $this->_clientSecret,
        ]);

        return $provider;
    }
}
