<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181023_212855_add_column_partner_verification_date migration.
 */
class m181023_212855_add_column_partner_verification_date extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'verificationStartDate', $this->date());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'verificationStartDate');

        return true;
    }
}
