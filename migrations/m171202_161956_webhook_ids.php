<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171202_161956_webhook_ids migration.
 */
class m171202_161956_webhook_ids extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // BitBucket uses UUIDs, so don't assume this will be an int. We're not indexing it anyway.
        $this->addColumn('craftcom_packages', 'webhookId', $this->string()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171202_161956_webhook_ids cannot be reverted.\n";
        return false;
    }
}
