<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171122_005228_webhook_secret migration.
 */
class m171122_005228_webhook_secret extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('craftcom_packages', 'webhookToken', 'webhookSecret');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171122_005228_webhook_secret cannot be reverted.\n";
        return false;
    }
}
