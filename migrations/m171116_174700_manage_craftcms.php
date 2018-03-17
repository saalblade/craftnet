<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m171116_174700_manage_craftcms migration.
 */
class m171116_174700_manage_craftcms extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->update('craftcom_packages', [
            'repository' => 'https://github.com/craftcms/cms',
            'managed' => true,
        ], [
            'name' => 'craftcms/cms',
        ], [], false);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171116_174700_manage_craftcms cannot be reverted.\n";
        return false;
    }
}
