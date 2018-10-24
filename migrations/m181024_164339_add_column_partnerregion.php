<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181024_164339_add_column_partnerregion migration.
 */
class m181024_164339_add_column_partnerregion extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'region', $this->string());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'region');

        return true;
    }
}
