<?php

namespace craftnet\controllers\id;

use Craft;
use craft\commerce\Plugin as Commerce;
use craft\web\Controller;
use craftnet\Module;
use Throwable;
use yii\web\Response;

/**
 * Class InvoicesController
 */
class InvoicesController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Get invoices.
     *
     * @return Response
     */
    public function actionGetInvoices(): Response
    {
        $this->requireLogin();
        $user = Craft::$app->getUser()->getIdentity();

        try {
            $customer = Commerce::getInstance()->getCustomers()->getCustomerByUserId($user->id);

            $invoices = [];

            if ($customer) {
                $invoices = Module::getInstance()->getInvoiceManager()->getInvoices($customer);
            }

            return $this->asJson($invoices);
        } catch (Throwable $e) {
            return $this->asErrorJson($e->getMessage());
        }
    }
}
