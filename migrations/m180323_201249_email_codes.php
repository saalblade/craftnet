<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m180323_201249_email_codes migration.
 */
class m180323_201249_email_codes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftnet_emailcodes', [
            'id' => $this->primaryKey(),
            'userId' => $this->integer(),
            'email' => $this->string()->notNull(),
            'code' => $this->string()->notNull(),
            'dateIssued' => $this->dateTime()->notNull(),
        ]);

        $this->addForeignKey(null, 'craftnet_emailcodes', ['userId'], 'users', ['id'], 'CASCADE');
        $this->createIndex(null, 'craftnet_emailcodes', ['userId', 'email']);
        $this->createIndex(null, 'craftnet_emailcodes', ['dateIssued']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180323_201249_email_codes cannot be reverted.\n";
        return false;
    }
}
