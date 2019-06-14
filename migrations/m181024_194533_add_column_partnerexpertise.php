<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181024_194533_add_column_partnerexpertise migration.
 */
class m181024_194533_add_column_partnerexpertise extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'expertise', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

        $this->dropColumn('craftnet_partners', 'expertise');

        return true;
    }
}
