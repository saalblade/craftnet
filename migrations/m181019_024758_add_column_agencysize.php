<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181019_024758_add_column_agencysize migration.
 */
class m181019_024758_add_column_agencysize extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            'craftnet_partners',
            'agencySize',
            $this->integer()->after('isRegisteredBusiness')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropcolumn('craftnet_partners', 'agencySize');

        return true;
    }
}
