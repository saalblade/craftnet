<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171207_185052_craft2_plugin_hits_tweak migration.
 */
class m171207_185052_craft2_plugin_hits_tweak extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Manually construct the SQL for Postgres
        // (see https://github.com/yiisoft/yii2/issues/12077)
        $this->execute('alter table [[craftcom_craft2pluginhits]] alter column [[hits]] set not null, alter column [[hits]] set default 1');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171207_185052_craft2_plugin_hits_tweak cannot be reverted.\n";
        return false;
    }
}
