<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171114_012315_new_plugin_columns migration.
 */
class m171114_012315_new_plugin_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_plugins', 'pendingApproval', $this->boolean()->defaultValue(false));
        $this->addColumn('craftcom_plugins', 'changelog', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171114_012315_new_plugin_columns cannot be reverted.\n";
        return false;
    }
}
