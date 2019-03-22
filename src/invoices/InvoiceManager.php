<?php

namespace craftnet\invoices;

use craft\db\Query;
use craft\commerce\elements\Order;
use craft\commerce\models\Customer;
use craft\helpers\UrlHelper;
use craftnet\Module;
use yii\base\Component;
use craft\helpers\Db;

class InvoiceManager extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get invoices.
     *
     * @param Customer $customer
     * @param string|null $searchQuery
     * @param int $limit
     * @param $page
     * @param $orderBy
     * @param $ascending
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getInvoices(Customer $customer, string $searchQuery = null, int $limit, $page, $orderBy, $ascending): array
    {
        $query = $this->_createInvoiceQuery($customer);

        $searchQuery = strtolower($searchQuery);
        $perPage = $limit;
        $offset = ($page - 1) * $perPage;

        if ($searchQuery) {
            $query->andWhere(['or',
                ['ilike', 'commerce_orders.number', $searchQuery],
            ]);
        }

        if ($orderBy) {
            $query->orderBy([$orderBy => $ascending ? SORT_ASC : SORT_DESC]);
        }

        $query
            ->offset($offset)
            ->limit($limit);

        $results = $query->all();

        return $this->transformInvoices($customer, $results);
    }

    /**
     * Get total invoices.
     *
     * @param Customer $customer
     * @param string|null $query
     * @return int
     */
    public function getTotalInvoices(Customer $customer, string $query = null): int
    {
        $invoiceQuery = $this->_createInvoiceQuery($customer);

        if ($query) {
            $invoiceQuery->andFilterWhere(['like', 'email', $query]);
        }

        return $invoiceQuery->count();
    }

    /**
     * Get invoice by its number.
     *
     * @param Customer $customer
     * @param $number
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function getInvoiceByNumber(Customer $customer, $number)
    {
        $query = $this->_createInvoiceQuery($customer);
        $query->andWhere(Db::parseParam('commerce_orders.number', $number));

        $result = $query->one();

        return $this->transformInvoice($customer, $result);
    }

    // Private Methods
    // =========================================================================

    /**
     * @param Customer $customer
     * @param $results
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function transformInvoices(Customer $customer, $results)
    {
        $orders = [];

        foreach ($results as $result) {
            $order = $this->transformInvoice($customer, $result);


            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * @param Customer $customer
     * @param $result
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    private function transformInvoice(Customer $customer, $result)
    {
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

        // Plugin licenses
        $order['pluginLicenses'] = Module::getInstance()->getPluginLicenseManager()->transformLicensesForOwner($result->pluginLicenses, $customer->getUser());

        return $order;
    }

    /**
     * @param Customer $customer
     * @return Query
     */
    private function _createInvoiceQuery(Customer $customer): Query
    {
        $query = Order::find();
        $query->customer($customer);
        $query->isCompleted(true);
        $query->limit(null);
        $query->orderBy('dateOrdered desc');

        return $query;
    }
}
