<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181105_203112_add_column_websiteslug migration.
 */
class m181105_203112_add_column_websiteslug extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'websiteSlug', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'websiteSlug');

        return true;
    }
}
