<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181019_211606_partner_bio_columns migration.
 */
class m181019_211606_partner_bio_columns extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn(
            'craftnet_partners',
            'shortBio',
            $this->string()->after('businessSummary')
        );

        $this->renameColumn('craftnet_partners', 'businessSummary', 'fullBio');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('craftnet_partners', 'shortBio');
        $this->renameColumn('craftnet_partners', 'fullBio', 'businessSummary');

        return true;
    }
}
