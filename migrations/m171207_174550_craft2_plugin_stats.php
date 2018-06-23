<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171207_174550_craft2_plugin_stats migration.
 */
class m171207_174550_craft2_plugin_stats extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_craft2pluginhits', [
            'plugin' => $this->string(),
            'hits' => $this->integer(),
            'PRIMARY KEY(plugin)',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171207_174550_craft2_plugin_stats cannot be reverted.\n";
        return false;
    }
}
