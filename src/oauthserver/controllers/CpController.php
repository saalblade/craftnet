<?php

namespace craftcom\oauthserver\controllers;

use yii\web\Response;

/**
 * Class CpController
 *
 * @package craftcom\oauthserver\controllers\v1
 */
class CpController extends BaseApiController
{
    /**
     * Handles /oauth requests.
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        return $this->renderTemplate('oauth-server/index');
    }
}
