<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180316_205417_developer_api_tokens migration.
 */
class m180316_205417_developer_api_tokens extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftcom_developers', 'apiToken', $this->char(60)->null());
        $this->createIndex(null, 'craftcom_developers', ['apiToken']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180316_205417_developer_api_tokens cannot be reverted.\n";
        return false;
    }
}
