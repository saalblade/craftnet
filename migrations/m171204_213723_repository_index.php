<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171204_213723_repository_index migration.
 */
class m171204_213723_repository_index extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $name = $this->db->getIndexName('craftcom_packages', ['repository']);
        $this->createIndex($name, 'craftcom_packages', ['lower([[repository]])']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171204_213723_repository_index cannot be reverted.\n";
        return false;
    }
}
