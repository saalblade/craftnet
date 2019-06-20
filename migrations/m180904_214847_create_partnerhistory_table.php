<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m180904_214847_create_partnerhistory_table migration.
 */
class m180904_214847_create_partnerhistory_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftnet_partnerhistory', [
            'id' => $this->primaryKey(),
            'authorId' => $this->integer(),
            'partnerId' => $this->integer()->notNull(),
            'message' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->addForeignKey(null, 'craftnet_partnerhistory', ['partnerId'], 'craftnet_partners', ['id'], 'CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists('craftnet_partnerhistory');
        return true;
    }
}
