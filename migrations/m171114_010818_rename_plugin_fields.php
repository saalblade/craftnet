<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craftcom\fields\Plugins;

/**
 * m171114_010818_rename_plugin_fields migration.
 */
class m171114_010818_rename_plugin_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('fields', ['type' => Plugins::class], ['type' => 'craftcom\cp\fields\Plugins']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171114_010818_rename_plugin_fields cannot be reverted.\n";
        return false;
    }
}
