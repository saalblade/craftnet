<?php

namespace craft\contentmigrations;

use craft\db\Migration;

/**
 * m180320_195525_rename_sequences migration.
 */
class m180320_195525_rename_sequences extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $names = [
            'craftcom_packagedeps_id_seq',
            'craftcom_packages_id_seq',
            'craftcom_packageversions_id_seq',
            'craftcom_plugincategories_id_seq',
            'craftcom_pluginhistory_id_seq',
            'craftcom_pluginscreenshots_id_seq',
        ];

        foreach ($names as $name) {
            $this->renameSequence($name, 'craftnet_'.substr($name, 9));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180320_195525_rename_sequences cannot be reverted.\n";
        return false;
    }
}
