<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171218_165358_keywords_plugin_column migration.
 */
class m171218_165358_keywords_plugin_column extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_plugins', 'keywords', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_165358_keywords_plugin_column cannot be reverted.\n";
        return false;
    }
}
