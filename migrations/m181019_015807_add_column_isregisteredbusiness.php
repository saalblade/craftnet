<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181019_015807_add_column_isregisteredbusiness migration.
 */
class m181019_015807_add_column_isregisteredbusiness extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            'craftnet_partners',
            'isRegisteredBusiness',
            $this->boolean()->defaultValue(false)->after('isEnterpriseVerified')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropcolumn('craftnet_partners', 'isRegisteredBusiness');

        return true;
    }
}
