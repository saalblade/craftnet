<?php

namespace craftcom\id\controllers;

use Craft;
use craftcom\id\controllers\BaseApiController;
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

    private $_scope = 'user:email,write:repo_hook';

    private $_clientId = 'b69e4b894ebf1c020d30sla';

    /**
     * Handles /connect requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $hash = hash('sha256', microtime(true).Craft::$app->getSecurity()->generateRandomString().Craft::$app->getRequest()->getUserIp());
        Craft::$app->getSession()->set('state', $hash);

        $params = array(
            'client_id' => $this->_clientId,
            'scope' => $this->_scope,
            'state' => $hash,
        );

        return $this->renderTemplate('developer/_connect', ['url' => $this->_authorizeUrl.'?'.urldecode(http_build_query($params))]);
    }
}
