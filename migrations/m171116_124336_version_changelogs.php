<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171116_124336_version_changelogs migration.
 */
class m171116_124336_version_changelogs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('craftcom_plugins', 'changelog');
        $this->addColumn('craftcom_packageversions', 'changelog', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171116_124336_version_changelogs cannot be reverted.\n";
        return false;
    }
}
