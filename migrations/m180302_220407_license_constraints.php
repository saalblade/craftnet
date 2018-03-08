<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180302_220407_license_constraints migration.
 */
class m180302_220407_license_constraints extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex(null, 'craftcom_cmslicenses', ['key'], true);
        $this->createIndex(null, 'craftcom_pluginlicenses', ['key'], true);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180302_220407_license_constraints cannot be reverted.\n";
        return false;
    }
}
