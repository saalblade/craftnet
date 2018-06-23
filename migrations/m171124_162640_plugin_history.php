<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171124_162640_plugin_history migration.
 */
class m171124_162640_plugin_history extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_pluginhistory', [
            'id' => $this->primaryKey(),
            'pluginId' => $this->integer()->notNull(),
            'note' => $this->string()->notNull(),
            'devComments' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, 'craftcom_pluginhistory', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171124_162640_plugin_history cannot be reverted.\n";
        return false;
    }
}
