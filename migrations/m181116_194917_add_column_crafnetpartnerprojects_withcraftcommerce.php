<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181116_194917_add_column_crafnetpartnerprojects_withcraftcommerce migration.
 */
class m181116_194917_add_column_crafnetpartnerprojects_withcraftcommerce extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            'craftnet_partnerprojects',
            'withCraftCommerce',
            $this->boolean()->defaultValue(false)->notNull()
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partnerprojects', 'withCraftCommerce');

        return true;
    }
}
