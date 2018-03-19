<?php

namespace craft\contentmigrations;

use craft\db\Migration;
use craft\helpers\MigrationHelper;
use craftnet\fields\Plugins;

/**
 * m180318_221648_craftnet migration.
 */
class m180318_221648_craftnet extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        foreach ($this->db->getSchema()->getTableNames() as $name) {
            if (strncmp($name, 'craftcom_', 9) === 0) {
                $this->renameTable($name, 'craftnet_'.substr($name, 9));
            }
        }

        $this->update('userpermissions', [
            'name' => 'craftnet:manageplugins',
        ], [
            'name' => 'craftcom:manageplugins'
        ], [], false);

        $this->update('fields', ['type' => Plugins::class], [
            'type' => ['craftcom\fields\Plugins', 'craftcom\cp\fields\Plugins']
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180318_221648_craftnet cannot be reverted.\n";
        return false;
    }
}
