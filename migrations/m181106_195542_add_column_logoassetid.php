<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181106_195542_add_column_logoassetid migration.
 */
class m181106_195542_add_column_logoassetid extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'logoAssetId', $this->integer());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'logoAssetId');

        return false;
    }
}
