<?php

namespace craftnet\oauthserver\controllers;

use yii\web\Response;

/**
 * Class CpController
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
