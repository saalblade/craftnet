<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181019_214421_partner_project_columns migration.
 */
class m181019_214421_partner_project_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('craftnet_partnerprojects', 'name', $this->string()->after('partnerId'));
        $this->addColumn('craftnet_partnerprojects', 'role', $this->string()->after('name'));
        $this->dropColumn('craftnet_partnerprojects', 'private');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partnerprojects', 'name');
        $this->dropColumn('craftnet_partnerprojects', 'role');
        $this->addColumn('craftnet_partnerprojects', 'private', $this->boolean()->after('url'));

        return true;
    }
}
