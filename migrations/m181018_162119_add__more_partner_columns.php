<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m181018_162119_add__more_partner_columns migration.
 */
class m181018_162119_add__more_partner_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            'craftnet_partners',
            'hasFullTimeDev',
            $this->boolean()->defaultValue(false)->after('minimumBudget')
        );

        $this->addColumn(
            'craftnet_partners',
            'isCraftVerified',
            $this->boolean()->defaultValue(false)->after('hasFullTimeDev')
        );

        $this->addColumn(
            'craftnet_partners',
            'isCommerceVerified',
            $this->boolean()->defaultValue(false)->after('isCraftVerified')
        );

        $this->addColumn(
            'craftnet_partners',
            'isEnterpriseVerified',
            $this->boolean()->defaultValue(false)->after('isCommerceVerified')
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'hasFullTimeDev');
        $this->dropColumn('craftnet_partners', 'isCraftVerified');
        $this->dropColumn('craftnet_partners', 'isCommerceVerified');
        $this->dropColumn('craftnet_partners', 'isEnterpriseVerified');

        return true;
    }
}
