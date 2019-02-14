<?php

namespace craftnet\controllers\id;

use Craft;
use craftnet\Module;
use yii\web\Response;

/**
 * Class SalesController
 */
class SalesController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Get sales.
     *
     * @return Response
     */
    public function actionGetSales(): Response
    {
        $this->requireLogin();

        $currentUser = Craft::$app->getUser()->getIdentity();

        $data = Module::getInstance()->getPluginLicenseManager()->getSalesArrayByPluginOwner($currentUser);

        return $this->asJson($data);
    }
}
