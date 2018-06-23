<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;

/**
 * m171130_194847_vcs_tokens_table migration.
 */
class m171130_194847_vcs_tokens_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        MigrationHelper::renameTable('oauthtokens', 'craftcom_vcstokens', $this);;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171130_194847_vcs_tokens_table cannot be reverted.\n";
        return false;
    }
}
