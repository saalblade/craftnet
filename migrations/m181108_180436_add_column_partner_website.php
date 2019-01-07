<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181108_180436_add_column_partner_website migration.
 */
class m181108_180436_add_column_partner_website extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partners', 'website', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {

        $this->dropColumn('craftnet_partners', 'website');

        return true;
    }
}
