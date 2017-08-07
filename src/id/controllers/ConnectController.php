<?php

namespace craftcom\id\controllers;

use Craft;
use craftcom\id\controllers\BaseApiController;
use craft\helpers\Json;
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
    private $_scope = 'user:email,write:repo_hook';

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
        $state = hash('sha256', microtime(true).Craft::$app->getSecurity()->generateRandomString().Craft::$app->getRequest()->getUserIp());
        Craft::$app->getSession()->set('state', $state);

        $params = [
            'client_id' => $this->_clientId,
            'scope' => $this->_scope,
            'state' => $state,
        ];

        return $this->renderTemplate('developer/_connect', ['url' => $this->_authorizeUrl.'?'.urldecode(http_build_query($params))]);
    }

    public function actionValidate(): Response
    {
        $code = Craft::$app->getRequest()->getParam('code');
        $state = Craft::$app->getRequest()->getParam('state');

        if (!$code || !$state) {
            // Exception?
        }

        if ($state !== Craft::$app->getSession()->get('state')) {
            // Exception?
        }

        $params = [
            'client_id' => $this->_clientId,
            'client_secret' => $this->_clientSecret,
            'state' => Craft::$app->getSession()->get('state'),
            'code' => $code,
        ];

        // try/catch
        $httpClient = Craft::createGuzzleClient(['headers' => ['Accept' => 'application/json']]);
        $response = $httpClient->request('post', $this->_tokenUrl, [
            'json' => $params,
        ]);

        if ($response->getStatusCode() === 200) {
            $responseBody = (string)$response->getBody();
            $responseBody = Json::decodeIfJson($responseBody);

            // Something went wrong.
            if (!is_array($responseBody)) {
                // something fucked up.
            }

            if (isset($responseBody['error'])) {
                // something gracefully fucked up.
            }

            $accessToken = $responseBody['access_token'];

            $client = new \Github\Client();
            $client->authenticate($accessToken, null, \Github\Client::AUTH_HTTP_TOKEN);
            $test = $client->me()->show();



            return $this->renderTemplate('developer/_validate', ['url' => $this->_authorizeUrl.'?'.urldecode(http_build_query($params))]);
        }

        //$client = new \Github\Client();
        //$test = $client->authenticate($code, null, \Github\Client::AUTH_HTTP_TOKEN);
        //$test2 = $client->me();
    }
}
