<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;
use craftnet\plugins\Plugin;

/**
 * m180424_205710_fix_craftcom_elements migration.
 */
class m180424_205710_fix_craftcom_elements extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('elements', ['type' => Plugin::class], ['type' => 'craftcom\plugins\Plugin'], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180424_205710_fix_craftcom_elements cannot be reverted.\n";
        return false;
    }
}
