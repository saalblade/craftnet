<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171121_170117_package_webhook_token migration.
 */
class m171121_170117_package_webhook_token extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_packages', 'webhookToken', $this->string(32)->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171121_170117_package_webhook_token cannot be reverted.\n";
        return false;
    }
}
