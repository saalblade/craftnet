<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180323_201227_ledger_tweaks migration.
 */
class m180323_201227_ledger_tweaks extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_developerledger', 'type', $this->string()->null());
        $this->addColumn('craftnet_developerledger', 'country', $this->char(2)->null());
        $this->addColumn('craftnet_developerledger', 'isEuMember', $this->boolean()->null());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180323_201227_ledger_tweaks cannot be reverted.\n";
        return false;
    }
}
