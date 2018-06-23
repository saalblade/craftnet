<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * m180531_210309_fix_primary_addresses migration.
 */
class m180531_210309_fix_primary_addresses extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $badAddresses = (new Query())
            ->select(['c.id'])
            ->from(['commerce_customers c'])
            ->leftJoin('commerce_customers_addresses ca', ['and',
                '[[ca.customerId]] = [[c.id]]',
                '[[ca.addressId]] = [[c.primaryBillingAddressId]]'
            ])
            ->where(['not', ['c.primaryBillingAddressId' => null]])
            ->andWhere(['ca.id' => null])
            ->column();

        $this->update('commerce_customers', [
            'primaryBillingAddressId' => null
        ], [
            'id' => $badAddresses
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180531_210309_fix_primary_addresses cannot be reverted.\n";
        return false;
    }
}
