<?php

namespace craftnet\sales;

use craft\db\Query;
use craft\elements\User;
use craft\helpers\ArrayHelper;
use craftnet\plugins\Plugin;
use craftnet\plugins\PluginEdition;
use yii\base\Component;

class SaleManager extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get sales by plugin owner.
     *
     * @param User $owner
     * @param string|null $searchQuery
     * @param $limit
     * @param $page
     * @return array
     */
    public function getSalesByPluginOwner(User $owner, string $searchQuery = null, $limit, $page): array
    {
        $defaultLimit = 30;
        $perPage = $limit ?? $defaultLimit;
        $offset = ($page - 1) * $perPage;

        $query = $this->_getSalesQuery($owner, $searchQuery);

        $query
            ->offset($offset)
            ->limit($limit);

        $results = $query->all();

        foreach ($results as &$row) {
            $row['netAmount'] = number_format($row['grossAmount'] * 0.8, 2);

            // Plugin
            $hasMultipleEditions = false;
            $plugin = Plugin::findOne($row['pluginId']);

            if ($plugin) {
                $editions = $plugin->getEditions();

                if ($editions) {
                    $hasMultipleEditions = count($editions) > 1;
                }
            }

            $row['plugin'] = [
                'id' => $row['pluginId'],
                'name' => $row['pluginName'],
                'hasMultipleEditions' => $hasMultipleEditions,
            ];

            // Customer
            $row['customer'] = [
                'id' => $row['ownerId'],
                'name' => implode(' ', array_filter([$row['ownerFirstName'], $row['ownerLastName']])),
                'email' => $row['ownerEmail'] ?? $row['orderEmail'],
            ];

            // Edition
            $edition = PluginEdition::findOne($row['editionId']);

            $row['edition'] = [
                'name' => $edition['name'],
                'handle' => $edition['handle'],
            ];

            // Unset attributes we don’t need anymore
            unset($row['pluginId'], $row['pluginName'], $row['ownerId'], $row['ownerFirstName'], $row['ownerLastName'], $row['ownerEmail']);
        }

        // Adjustments
        $results = ArrayHelper::index($results, 'id');
        $lineItemIds = array_keys($results);

        $adjustments = (new Query())
            ->select(['lineItemId', 'name', 'amount'])
            ->from(['commerce_orderadjustments'])
            ->where(['lineItemId' => $lineItemIds])
            ->all();

        foreach ($adjustments as $adjustment) {
            $results[$adjustment['lineItemId']]['adjustments'][] = $adjustment;
        }

        $results = array_values($results);

        return $results;
    }

    /**
     * Get total sales by plugin owner.
     *
     * @param User $owner
     * @param string|null $searchQuery
     * @return int|string
     */
    public function getTotalSalesByPluginOwner(User $owner, string $searchQuery = null)
    {
        $query = $this->_getSalesQuery($owner, $searchQuery);

        return $query->count();
    }

    // Private Methods
    // =========================================================================

    /**
     * Get sales query.
     *
     * @param User $owner
     * @param string|null $searchQuery
     * @return Query
     */
    private function _getSalesQuery(User $owner, string $searchQuery = null): Query
    {
        $query = (new Query())
            ->select([
                'lineitems.id AS id',
                'plugins.id AS pluginId',
                'plugins.name AS pluginName',
                'lineitems.total AS grossAmount',
                'users.id AS ownerId',
                'users.firstName AS ownerFirstName',
                'users.lastName AS ownerLastName',
                'users.email AS ownerEmail',
                'lineitems.dateCreated AS saleTime',
                'orders.email AS orderEmail',
                'elements.type AS purchasableType',
                'licenses.editionId AS editionId',
            ])
            ->from(['craftnet_pluginlicenses_lineitems licenses_items'])
            ->innerJoin('commerce_lineitems lineitems', '[[lineitems.id]] = [[licenses_items.lineItemId]]')
            ->innerJoin('commerce_orders orders', '[[orders.id]] = [[lineitems.orderId]]')
            ->innerJoin('craftnet_pluginlicenses licenses', '[[licenses.id]] = [[licenses_items.licenseId]]')
            ->innerJoin('craftnet_plugins plugins', '[[plugins.id]] = [[licenses.pluginId]]')
            ->leftJoin('users', '[[users.id]] = [[licenses.ownerId]]')
            ->leftJoin('elements', '[[elements.id]] = [[lineitems.purchasableId]]')
            ->where(['plugins.developerId' => $owner->id])
            ->orderBy(['lineitems.dateCreated' => SORT_DESC]);

        if ($searchQuery) {
            $query->andWhere([
                'or',
                ['ilike', 'orders.email', $searchQuery],
                ['ilike', 'plugins.name', $searchQuery],
                ['ilike', 'plugins.handle', $searchQuery],
            ]);
        }

        return $query;
    }
}
