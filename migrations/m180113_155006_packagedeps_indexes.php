<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m180113_155006_packagedeps_indexes migration.
 */
class m180113_155006_packagedeps_indexes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createIndex(null, 'craftcom_packagedeps', ['packageId', 'name']);
        $this->createIndex(null, 'craftcom_packagedeps', ['versionId', 'name']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180113_155006_packagedeps_indexes cannot be reverted.\n";
        return false;
    }
}
