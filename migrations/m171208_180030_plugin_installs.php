<?php

namespace craft\contentmigrations;

use Craft;
use craft\db\Migration;

/**
 * m171208_180030_plugin_installs migration.
 */
class m171208_180030_plugin_installs extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('craftcom_installedplugins', [
            'craftLicenseKey' => $this->string()->notNull(),
            'pluginId' => $this->integer()->notNull(),
            'lastActivity' => $this->dateTime()->notNull(),
            'PRIMARY KEY([[craftLicenseKey]], [[pluginId]])',
        ]);

        $this->addForeignKey(null, 'craftcom_installedplugins', ['pluginId'], 'craftcom_plugins', ['id'], 'CASCADE');

        $this->addColumn('craftcom_plugins', 'activeInstalls', $this->integer()->notNull()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171208_180030_plugin_installs cannot be reverted.\n";
        return false;
    }
}
