<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181115_204831_add_column_partnerproject_linktype migration.
 */
class m181115_204831_add_column_partnerproject_linktype extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partnerprojects', 'linkType', $this->text()->defaultValue('website'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partnerprojects', 'linkType');

        return true;
    }
}
