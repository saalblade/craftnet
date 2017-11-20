<?php

namespace craftcom\controllers\api\github;

use Craft;
use craftcom\controllers\api\BaseApiController;

/**
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  3.0
 */
class DefaultController extends BaseApiController
{
    public function actionPush()
    {
        $this->requirePostRequest();

        $payLoad = $this->getRequestBody();
    }
}
