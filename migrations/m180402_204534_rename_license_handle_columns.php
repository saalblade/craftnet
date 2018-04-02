<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180402_204534_rename_license_handle_columns migration.
 */
class m180402_204534_rename_license_handle_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->renameColumn('craftnet_cmslicenses', 'edition', 'editionHandle');
        $this->renameColumn('craftnet_pluginlicenses', 'plugin', 'pluginHandle');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180402_204534_rename_license_handle_columns cannot be reverted.\n";
        return false;
    }
}
