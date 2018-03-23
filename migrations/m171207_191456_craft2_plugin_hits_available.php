<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171207_191456_craft2_plugin_hits_available migration.
 */
class m171207_191456_craft2_plugin_hits_available extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_craft2pluginhits', 'available', $this->boolean()->defaultValue(false));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171207_191456_craft2_plugin_hits_available cannot be reverted.\n";
        return false;
    }
}
