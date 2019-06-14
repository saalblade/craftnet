<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m180717_101158_fix_order_locales migration.
 */
class m180717_101158_fix_order_locales extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('commerce_orders', [
            'orderLocale' => 'en-US'
        ], [
            'orderLocale' => null
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180717_101158_fix_order_locales cannot be reverted.\n";
        return false;
    }
}
