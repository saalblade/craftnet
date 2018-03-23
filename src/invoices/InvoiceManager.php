<?php

namespace craftnet\invoices;

use craftnet\Module;
use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\helpers\UrlHelper;
use yii\base\Component;

class InvoiceManager extends Component
{
    public function getInvoices(Customer $customer)
    {
        $query = Order::find();
        $query->customer($customer);
        $query->isCompleted(true);
        $query->limit(null);
        $query->orderBy('dateOrdered desc');

        $results = $query->all();

        $orders = [];

        foreach ($results as $result) {
            $order = $result->getAttributes(['number', 'datePaid', 'shortNumber', 'itemTotal', 'totalPrice', 'billingAddress', 'pdfUrl']);
            $order['pdfUrl'] = UrlHelper::actionUrl("commerce/downloads/pdf?number={$result->number}");

            // Line Items

            $lineItems = [];

            foreach ($result->lineItems as $lineItem) {
                $lineItems[] = $lineItem->getAttributes([
                    'description',
                    'salePrice',
                    'qty',
                    'subtotal',
                ]);
            }

            $order['lineItems'] = $lineItems;


            // Transactions

            $transactionResults = $result->getTransactions();

            $transactions = [];

            foreach ($transactionResults as $transactionResult) {
                $transactionGateway = $transactionResult->getGateway();

                $transactions[] = [
                    'type' => $transactionResult->type,
                    'status' => $transactionResult->status,
                    'amount' => $transactionResult->amount,
                    'paymentAmount' => $transactionResult->paymentAmount,
                    'gatewayName' => ($transactionGateway ? $transactionGateway->name : null),
                    'dateCreated' => $transactionResult->dateCreated,
                ];
            }

            $order['transactions'] = $transactions;


            // CMS licenses

            $order['cmsLicenses'] = Module::getInstance()->getCmsLicenseManager()->transformLicensesForOwner($result->cmsLicenses, $customer->getUser());


            // CMS licenses

            $order['pluginLicenses'] = Module::getInstance()->getPluginLicenseManager()->transformLicensesForOwner($result->pluginLicenses, $customer->getUser());


            $orders[] = $order;
        }

        return $orders;
    }
}
