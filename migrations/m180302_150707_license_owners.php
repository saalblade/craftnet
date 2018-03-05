<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180302_150707_license_owners migration.
 */
class m180302_150707_license_owners extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_cmslicenses', 'ownerId', $this->integer()->null());
        $this->addForeignKey(null, 'craftcom_cmslicenses', ['ownerId'], 'users', ['id'], 'SET NULL');

        $this->addColumn('craftcom_pluginlicenses', 'ownerId', $this->integer()->null());
        $this->addForeignKey(null, 'craftcom_pluginlicenses', ['ownerId'], 'users', ['id'], 'SET NULL');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180302_150707_license_owners cannot be reverted.\n";
        return false;
    }
}
