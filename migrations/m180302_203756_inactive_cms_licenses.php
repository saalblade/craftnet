<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180302_203756_inactive_cms_licenses migration.
 */
class m180302_203756_inactive_cms_licenses extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_inactivecmslicenses', [
            'key' => $this->string(250)->notNull(),
            'data' => $this->text(),
            'PRIMARY KEY([[key]])',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180302_203756_inactive_cms_licenses cannot be reverted.\n";
        return false;
    }
}
