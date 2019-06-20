<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181024_215625_alter_partner_agencysize migration.
 */
class m181024_215625_alter_partner_agencysize extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->alterColumn('craftnet_partners', 'agencySize', 'string');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m181024_215625_alter_partner_agencysize cannot be reverted but going backwards is allowed.\n";
        return true;
    }
}
