<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181019_210735_drop_column_minimumbudget migration.
 */
class m181019_210735_drop_column_minimumbudget extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dropColumn('craftnet_partners', 'minimumBudget');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->addColumn('craftnet_partners', 'minimumBudget', $this->integer());

        return true;
    }
}
