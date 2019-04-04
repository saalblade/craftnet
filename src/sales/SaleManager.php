<?php

namespace craftnet\sales;

use craft\db\Query;
use craft\elements\User;
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
            $row['plugin'] = [
                'id' => $row['pluginId'],
                'name' => $row['pluginName']
            ];
            $row['customer'] = [
                'id' => $row['ownerId'],
                'name' => implode(' ', array_filter([$row['ownerFirstName'], $row['ownerLastName']])),
                'email' => $row['ownerEmail'] ?? $row['orderEmail'],
            ];

            unset($row['pluginId'], $row['pluginName'], $row['ownerId'], $row['ownerFirstName'], $row['ownerLastName'], $row['ownerEmail']);
        }

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
                'lineitems.salePrice AS grossAmount',
                'users.id AS ownerId',
                'users.firstName AS ownerFirstName',
                'users.lastName AS ownerLastName',
                'users.email AS ownerEmail',
                'lineitems.dateCreated AS saleTime',
                'orders.email AS orderEmail',
                'elements.type AS purchasableType',
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
            $query->andWhere(['or',
                ['ilike', 'orders.email', $searchQuery],
                ['ilike', 'plugins.name', $searchQuery],
                ['ilike', 'plugins.handle', $searchQuery],
            ]);
        }

        return $query;
    }
}
