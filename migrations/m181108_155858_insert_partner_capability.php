<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m181108_155858_insert_partner_capability migration.
 */
class m181108_155858_insert_partner_capability extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('craftnet_partnercapabilities', [
            'id' => 5,
            'title' => 'Ongoing Maintenance'
        ], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->delete('craftnet_partnercapabilities', ['id' => 5]);
        return true;
    }
}
